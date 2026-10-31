<?php

namespace App\Services\ContactCenter;

use App\Models\CallCenterAgents;
use App\Models\SupervisorAuditLog;
use App\Models\SupervisorScope;
use App\Services\FreeswitchEslService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SupervisorLiveControlService
{
    protected FreeswitchEslService $esl;
    protected AgentStateManager $agentStateManager;

    public function __construct(FreeswitchEslService $esl, AgentStateManager $agentStateManager)
    {
        $this->esl = $esl;
        $this->agentStateManager = $agentStateManager;
    }

    /**
     * Check if supervisor is authorized to perform action on a queue.
     */
    public function authorize(string $userUuid, ?string $queueUuid = null, string $action = 'listen'): bool
    {
        // Superadmins always authorized
        if (session('is_superadmin', false)) {
            return true;
        }

        $scope = SupervisorScope::where('user_uuid', $userUuid)->first();
        if (!$scope) {
            // Default to allowed for PBX tenant admins if no restrictive scope defined
            return true;
        }

        // Action check
        $canAction = match ($action) {
            'listen' => $scope->can_listen,
            'whisper' => $scope->can_whisper,
            'barge' => $scope->can_barge,
            'takeover' => $scope->can_takeover,
            'force_logout' => $scope->can_force_logout,
            default => true,
        };

        if (!$canAction) {
            return false;
        }

        // Queue check
        if ($queueUuid && !$scope->all_queues) {
            $assigned = $scope->assigned_queues ?? [];
            return in_array($queueUuid, $assigned, true);
        }

        return true;
    }

    /**
     * Listen (Eavesdrop) on live channel secretly.
     */
    public function listen(
        string $supervisorExtension,
        string $channelUuid,
        string $supervisorUserUuid,
        ?string $agentUuid = null,
        ?string $agentName = null,
        ?string $queueUuid = null
    ): array {
        $domainName = session('domain_name');
        $domainUuid = session('domain_uuid');

        $cmd = sprintf(
            "bgapi originate {origination_caller_id_name='Supervisor-Listen',origination_caller_id_number='*0'}user/%s@%s &eavesdrop(%s)",
            $supervisorExtension,
            $domainName,
            $channelUuid
        );

        return $this->executeAndAudit([
            'action' => 'LISTEN',
            'command' => $cmd,
            'supervisor_user_uuid' => $supervisorUserUuid,
            'supervisor_extension' => $supervisorExtension,
            'channel_uuid' => $channelUuid,
            'agent_uuid' => $agentUuid,
            'agent_name' => $agentName,
            'queue_uuid' => $queueUuid,
            'domain_uuid' => $domainUuid,
            'details' => ['mode' => 'secret_listen'],
        ]);
    }

    /**
     * Whisper (Coach): Supervisor talks to agent only, caller cannot hear.
     */
    public function whisper(
        string $supervisorExtension,
        string $channelUuid,
        string $supervisorUserUuid,
        ?string $agentUuid = null,
        ?string $agentName = null,
        ?string $queueUuid = null
    ): array {
        $domainName = session('domain_name');
        $domainUuid = session('domain_uuid');

        $cmd = sprintf(
            "bgapi originate {origination_caller_id_name='Supervisor-Coach',eavesdrop_whisper_bleg=true}user/%s@%s &eavesdrop(%s)",
            $supervisorExtension,
            $domainName,
            $channelUuid
        );

        return $this->executeAndAudit([
            'action' => 'WHISPER',
            'command' => $cmd,
            'supervisor_user_uuid' => $supervisorUserUuid,
            'supervisor_extension' => $supervisorExtension,
            'channel_uuid' => $channelUuid,
            'agent_uuid' => $agentUuid,
            'agent_name' => $agentName,
            'queue_uuid' => $queueUuid,
            'domain_uuid' => $domainUuid,
            'details' => ['mode' => 'agent_whisper_coach'],
        ]);
    }

    /**
     * Barge-in (3-way): Supervisor joins call as full participant.
     */
    public function barge(
        string $supervisorExtension,
        string $channelUuid,
        string $supervisorUserUuid,
        ?string $agentUuid = null,
        ?string $agentName = null,
        ?string $queueUuid = null
    ): array {
        $domainName = session('domain_name');
        $domainUuid = session('domain_uuid');

        $cmd = sprintf(
            "bgapi originate {origination_caller_id_name='Supervisor-Barge',eavesdrop_whisper_aleg=true,eavesdrop_whisper_bleg=true}user/%s@%s &eavesdrop(%s)",
            $supervisorExtension,
            $domainName,
            $channelUuid
        );

        return $this->executeAndAudit([
            'action' => 'BARGE',
            'command' => $cmd,
            'supervisor_user_uuid' => $supervisorUserUuid,
            'supervisor_extension' => $supervisorExtension,
            'channel_uuid' => $channelUuid,
            'agent_uuid' => $agentUuid,
            'agent_name' => $agentName,
            'queue_uuid' => $queueUuid,
            'domain_uuid' => $domainUuid,
            'details' => ['mode' => 'three_way_barge'],
        ]);
    }

    /**
     * Takeover / Transfer call to another extension.
     */
    public function takeover(
        string $channelUuid,
        string $destinationExtension,
        string $supervisorUserUuid,
        ?string $agentUuid = null,
        ?string $agentName = null,
        ?string $queueUuid = null
    ): array {
        $domainName = session('domain_name');
        $domainUuid = session('domain_uuid');

        $cmd = sprintf("api uuid_transfer %s %s XML %s", $channelUuid, $destinationExtension, $domainName);

        return $this->executeAndAudit([
            'action' => 'TAKEOVER',
            'command' => $cmd,
            'supervisor_user_uuid' => $supervisorUserUuid,
            'supervisor_extension' => $destinationExtension,
            'channel_uuid' => $channelUuid,
            'agent_uuid' => $agentUuid,
            'agent_name' => $agentName,
            'queue_uuid' => $queueUuid,
            'domain_uuid' => $domainUuid,
            'details' => ['destination' => $destinationExtension],
        ]);
    }

    /**
     * Force Drop live call.
     */
    public function forceDrop(
        string $channelUuid,
        string $supervisorUserUuid,
        ?string $reason = null,
        ?string $agentUuid = null,
        ?string $agentName = null,
        ?string $queueUuid = null
    ): array {
        $domainUuid = session('domain_uuid');
        $cmd = sprintf("api uuid_kill %s NORMAL_CLEARING", $channelUuid);

        return $this->executeAndAudit([
            'action' => 'FORCE_DROP',
            'command' => $cmd,
            'supervisor_user_uuid' => $supervisorUserUuid,
            'channel_uuid' => $channelUuid,
            'agent_uuid' => $agentUuid,
            'agent_name' => $agentName,
            'queue_uuid' => $queueUuid,
            'domain_uuid' => $domainUuid,
            'reason_description' => $reason,
            'details' => ['hangup_cause' => 'NORMAL_CLEARING'],
        ]);
    }

    /**
     * Force Logout an agent with audit reason.
     */
    public function forceLogoutAgent(
        string $agentUuid,
        string $supervisorUserUuid,
        ?string $reasonCode = null,
        ?string $reasonDescription = null
    ): array {
        $domainUuid = session('domain_uuid');
        $agent = CallCenterAgents::where('call_center_agent_uuid', $agentUuid)->first();

        try {
            $this->agentStateManager->setStatus($agentUuid, AgentStateManager::STATUS_LOGGED_OUT);
            $status = 'SUCCESS';
            $error = null;
        } catch (\Throwable $e) {
            $status = 'FAILED';
            $error = $e->getMessage();
        }

        SupervisorAuditLog::create([
            'domain_uuid' => $domainUuid,
            'supervisor_user_uuid' => $supervisorUserUuid,
            'action_type' => 'FORCE_LOGOUT',
            'target_agent_uuid' => $agentUuid,
            'target_agent_name' => $agent?->agent_name,
            'reason_code' => $reasonCode ?: 'SUPERVISOR_OVERRIDE',
            'reason_description' => $reasonDescription,
            'ip_address' => request()->ip(),
            'status' => $status,
            'details' => json_encode(['error' => $error]),
        ]);

        return [
            'success' => $status === 'SUCCESS',
            'agent_uuid' => $agentUuid,
            'status' => 'Logged Out',
            'error' => $error,
        ];
    }

    /**
     * Execute ESL command and write immutable audit log.
     */
    protected function executeAndAudit(array $data): array
    {
        $status = 'SUCCESS';
        $error = null;
        $response = null;

        try {
            $response = $this->esl->executeCommand($data['command'], false);
            if (is_string($response) && Str::startsWith(trim($response), '-ERR')) {
                $status = 'FAILED';
                $error = trim($response);
            }
        } catch (\Throwable $e) {
            $status = 'FAILED';
            $error = $e->getMessage();
            Log::error("SupervisorLiveControlService error: {$error}");
        }

        SupervisorAuditLog::create([
            'domain_uuid' => $data['domain_uuid'] ?? session('domain_uuid'),
            'supervisor_user_uuid' => $data['supervisor_user_uuid'],
            'supervisor_extension' => $data['supervisor_extension'] ?? null,
            'action_type' => $data['action'],
            'target_agent_uuid' => $data['agent_uuid'] ?? null,
            'target_agent_name' => $data['agent_name'] ?? null,
            'target_channel_uuid' => $data['channel_uuid'] ?? null,
            'target_queue_uuid' => $data['queue_uuid'] ?? null,
            'reason_code' => $data['reason_code'] ?? null,
            'reason_description' => $data['reason_description'] ?? null,
            'ip_address' => request()->ip(),
            'status' => $status,
            'details' => json_encode(array_merge($data['details'] ?? [], [
                'raw_response' => is_string($response) ? substr($response, 0, 500) : null,
                'error' => $error,
            ])),
        ]);

        return [
            'success' => $status === 'SUCCESS',
            'action' => $data['action'],
            'response' => $response,
            'error' => $error,
        ];
    }
}
