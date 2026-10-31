<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AccessControlController;
use App\Http\Controllers\ActiveConferenceController;
use App\Http\Controllers\ActiveCallsController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AiAgentController;
use App\Http\Controllers\AppsController;
use App\Http\Controllers\AppsCredentialsController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\BusinessHoursController;
use App\Http\Controllers\PagingGroupsController;
use App\Http\Controllers\BridgeController;
use App\Http\Controllers\BasicDialerController;
use App\Http\Controllers\BasicQueueController;
use App\Http\Controllers\CallBlockController;
use App\Http\Controllers\CallRecordingController;
use App\Http\Controllers\CallFlowController;
use App\Http\Controllers\CallRoutingOptionsController;
use App\Http\Controllers\CdrsController;
use App\Http\Controllers\ConferenceCenterController;
use App\Http\Controllers\ConferenceController;
use App\Http\Controllers\ConferenceControlController;
use App\Http\Controllers\ConferenceProfileController;
use App\Http\Controllers\ConferenceRoomController;
use App\Http\Controllers\CsrfTokenController;
use App\Http\Controllers\DatabaseTransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceCloudProvisioningController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceImportExportController;
use App\Http\Controllers\DeviceKeyTemplateController;
use App\Http\Controllers\DeviceProfileController;
use App\Http\Controllers\DialplanController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\DomainGroupsController;
use App\Http\Controllers\DynamicRouteController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\ExtensionsController;
use App\Http\Controllers\ExtensionStatisticsController;
use App\Http\Controllers\FaxesController;
use App\Http\Controllers\FaxInboxController;
use App\Http\Controllers\FaxLogController;
use App\Http\Controllers\FaxSentController;
use App\Http\Controllers\FiberneticsMmsWebhookController;
use App\Http\Controllers\FirewallController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\LegacyProvisionTemplateController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessageMediaController;
use App\Http\Controllers\MessageSettingsController;
use App\Http\Controllers\MenuManagerController;
use App\Http\Controllers\MusicOnHoldController;
use App\Http\Controllers\PhoneNumbersController;
use App\Http\Controllers\PinNumberController;
use App\Http\Controllers\PolycomLogController;
use App\Http\Controllers\PolycomProvisioningFileController;
use App\Http\Controllers\ProFeaturesController;
use App\Http\Controllers\ProvisioningController;
use App\Http\Controllers\PhonebookController;
use App\Http\Controllers\PhonebookManagerController;
use App\Http\Controllers\RecordingsController;
use App\Http\Controllers\RecordingsManagerController;
use App\Http\Controllers\RegistrationsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RingGroupsController;
use App\Http\Controllers\SansayActiveCallsController;
use App\Http\Controllers\SansayRegistrationsController;
use App\Http\Controllers\ScheduledAnnouncementController;
use App\Http\Controllers\SipProfileController;
use App\Http\Controllers\SipStatusController;
use App\Http\Controllers\SpeedDialController;
use App\Http\Controllers\SwitchVariableController;
use App\Http\Controllers\SwitchModuleController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\SystemSettingsController;
use App\Http\Controllers\UserLogsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VirtualReceptionistController;
use App\Http\Controllers\VoicemailController;
use App\Http\Controllers\VoicemailMessagesController;
use App\Http\Controllers\WakeupCallsController;
use App\Http\Controllers\WhitelistedNumbersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/extensions/callerid', [ExtensionsController::class, 'callerID'])->withoutMiddleware(['auth'])->name('callerID');
Route::post('/extensions/{extension}/callerid/update/', [ExtensionsController::class, 'updateCallerID'])->withoutMiddleware(['auth'])->name('updateCallerID');

//Polycom log handling
Route::put('/polycom/log/{name}', [PolycomLogController::class, 'store'])->withoutMiddleware(['auth', 'web'])->name('log.store');
Route::get('/polycom/log/{name}', [PolycomLogController::class, 'show'])->withoutMiddleware(['auth', 'web'])->name('log.get');
// Route::get('/extensions', [ExtensionsController::class, 'index']) ->name('extensionsList');

// Webhooks
Route::webhooks('webhook/postmark', 'postmark');
Route::webhooks('webhook/mailgun', 'mailgun');
Route::webhooks('webhook/commio/sms', 'commio_messaging');
Route::webhooks('webhook/sinch/sms', 'sinch_messaging');
Route::webhooks('webhook/voxutel/sms', 'voxutel_messaging');
Route::webhooks('webhook/bandwidth/sms', 'bandwidth_messaging');
Route::webhooks('webhook/telnyx/sms', 'telnyx_messaging');
Route::webhooks('webhook/clicksend/sms', 'clicksend_messaging');
Route::webhooks('webhook/apidaze/sms', 'apidaze_messaging');
Route::webhooks('webhook/bulkvs/sms', 'bulkvs_messaging');
Route::webhooks('webhook/voipms/sms', 'voipms_messaging');
Route::webhooks('webhook/fibernetics/sms', 'fibernetics_messaging', 'get');
Route::post('webhook/fibernetics/sms', FiberneticsMmsWebhookController::class)
    ->name('webhook.fibernetics.mms');
Route::webhooks('webhook/twilio/sms', 'twilio_messaging');
Route::webhooks('/sms/ringotelwebhook', 'ringotel_messaging');
Route::webhooks('/webhook/freeswitch', 'freeswitch');
Route::webhooks('/webhook/stripe', 'stripe');
Route::webhooks('/webhook/assemblyai', 'assemblyai');

// Routes for 2FA email challenge. Used as a backup when 2FA is not enabled.
Route::get('/email-challenge', [App\Http\Controllers\Auth\EmailChallengeController::class, 'create'])->name('email-challenge.login');
Route::put('/email-challenge', [App\Http\Controllers\Auth\EmailChallengeController::class, 'update'])
    ->middleware('throttle:2,1')
    ->name('email-challenge.new-code');
Route::post('/email-challenge', [App\Http\Controllers\Auth\EmailChallengeController::class, 'store']);

// Message media URL for MMS. Carriers fetch it without a session. Skipping the web stack
// keeps the legacy PHP session's no-store headers and file lock off, so browsers cache photos.
Route::get(
    '/messages/media/{message_uuid}/{index}/{file_name?}',
    [MessageMediaController::class, 'show']
)->withoutMiddleware('web')->name('messages.media.show');

// Csrf token
Route::get('/csrf-token/refresh', [CsrfTokenController::class, 'store']);

// Get mobile app password
Route::get('/mobile-app/qr-code', [AppsCredentialsController::class, 'showQrCode'])
    ->middleware('signed')
    ->name('appsMobileAppQr');
Route::get('/mobile-app/get-password/{token}', [AppsCredentialsController::class, 'getPasswordByToken'])->name('appsGetPasswordByToken');
Route::post('/mobile-app/get-password/{token}', [AppsCredentialsController::class, 'retrievePasswordByToken'])->name('appsRetrievePasswordByToken');

Route::match(['PUT', 'GET', 'HEAD'], '/prov/{bucket}/{id}-{kind}.{ext}', [PolycomProvisioningFileController::class, 'handle'])
    ->whereIn('bucket', ['logs', 'phoneconfigs', 'directories', 'calls', 'corefiles'])
    ->where('id', '[0-9A-Fa-f]{12}')
    ->where('kind', '[A-Za-z0-9_-]+')
    ->where('ext', '[A-Za-z0-9]+')
    ->middleware(['throttle:provision', 'provision.digest'])
    ->withoutMiddleware(['auth', 'web'])
    ->name('provision.polycom-files');

// Device directory / phonebook XML. Must be registered before the /prov/{path}
// catch-all so it is not shadowed by it.
Route::match(['GET', 'HEAD'], '/prov/directory/{book}/{path}', [PhonebookController::class, 'serve'])
    ->where('book', '[0-9A-Fa-f-]{36}|all')
    ->where('path', '.*')
    ->middleware(['throttle:provision', 'provision.digest'])
    ->withoutMiddleware(['auth', 'web'])
    ->name('provision.directory');

Route::match(['GET', 'HEAD'], '/prov/{path}', [ProvisioningController::class, 'serve'])
    ->where('path', '.*')
    ->middleware(['throttle:provision', 'provision.digest'])
    ->name('provision.serve');

// Call Recordings
Route::get('/call-detail-records/recordings/{uuid}/stream', [CallRecordingController::class, 'stream'])->name('cdrs.recording.stream');
Route::get('/call-detail-records/recordings/{uuid}/download', [CallRecordingController::class, 'download'])->name('cdrs.recording.download');

// Signed voicemail recording download links sent by email.
Route::get('/voicemails/messages/{message_uuid}/public-download', [VoicemailMessagesController::class, 'downloadSignedVoicemailMessage'])
    ->name('voicemails.messages.public-download');

Route::group(['middleware' => 'auth'], function () {

    // Extensions
    Route::get('extensions', [ExtensionsController::class, 'index'])->name('extensions.index');
    Route::post('extensions/duplicate', [ExtensionsController::class, 'duplicate'])->name('extensions.duplicate');

    // Route::resource('extensions', ExtensionsController::class);
    Route::post('/extensions/{extension}/assign-device', [ExtensionsController::class, 'assignDevice'])->name('extensions.assign-device');
    Route::post('/extensions/{extension}/device', [ExtensionsController::class, 'oldStoreDevice'])->name('extensions.store-device');
    Route::get('/extensions/{extension}/device/{device}/edit', [ExtensionsController::class, 'oldEditDevice'])->name('extensions.edit-device');
    Route::put('/extensions/{extension}/device/{device}', [ExtensionsController::class, 'oldUpdateDevice'])->name('extensions.update-device');
    Route::delete('/extensions/{extension}/unassign/{deviceLine}/device', [ExtensionsController::class, 'unAssignDevice'])->name('extensions.unassign-device');
    Route::delete('/extensions/{extension}/callforward/{type}', [ExtensionsController::class, 'clearCallforwardDestination'])->name('extensions.clear-callforward-destination');
    Route::post('/extensions/{extension}/send-event-notify', [ExtensionsController::class, 'sendEventNotify'])->name('extensions.send-event-notify');
    Route::post('/extensions/send-event-notify-all', [ExtensionsController::class, 'sendEventNotifyAll'])->name('extensions.send-event-notify-all');
    Route::get('/extensions-export', [ExtensionsController::class, 'export'])->name('extensions.export');

    // Call Detail Records
    Route::get('/call-detail-records', [CdrsController::class, 'index'])->name('cdrs.index');
    Route::post('/call-detail-records', [CdrsController::class, 'index'])->name('cdrs.download');
    Route::post('/call-detail-records/export', [CdrsController::class, 'export'])->name('cdrs.export');

    //Extension Statistics
    Route::get('/extension-statistics', [ExtensionStatisticsController::class, 'index'])->name('extension-statistics.index');
    Route::get('/extension-statistics/data', [ExtensionStatisticsController::class, 'getData'])->name('extension-statistics.data');
    Route::get('/extension-statistics-export', [ExtensionStatisticsController::class, 'export'])->name('extension-statistics.export');

    //Domains
    Route::get('domains/extensions', [DomainController::class, 'countExtensionsInDomains']);
    Route::post('/domains/switch', [DomainController::class, 'switchDomain'])->name('switchDomain');
    Route::get('/domains/switch', function () {
        return redirect('/dashboard');
    });
    Route::get('/domains/switch/{domain}', [DomainController::class, 'switchDomainFusionPBX'])->name('switchDomainFusionPBX');
    Route::get('/domains/filter/', [DomainController::class, 'filterDomainsFusionPBX'])->name('filterDomainsFusionPBX');

    //Users
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    // Route::post('user/{user}/settings', [UserSettingsController::class, 'store'])->name('users.settings.store');
    // Route::delete('user/settings/{setting}', [UserSettingsController::class, 'destroy'])->name('users.settings.destroy');
    Route::post('user/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('users.password.email');

    // Groups
    Route::get('groups', [GroupsController::class, 'index'])->name('groups.index');
    Route::get('groups/{group}/permissions', [GroupsController::class, 'permissionsIndex'])->name('groups.permissions.index');

    // Menu Manager
    Route::get('menus', [MenuManagerController::class, 'index'])->name('menus.index');

    // Domains
    Route::get('domains', [DomainController::class, 'index'])->name('domains.index');

    //Fax
    Route::get('faxes', [FaxesController::class, 'index'])->name('faxes.index');
    Route::get('/fax/{fax}/inbox', [FaxInboxController::class, 'index'])->name('fax-inbox.index');
    Route::get('/fax/{fax}/sent', [FaxSentController::class, 'index'])->name('faxes.sent.index');
    Route::get('/faxes/active/{id}', [FaxesController::class, 'active'])->name('faxes.active.list');
    Route::get('/fax/{fax}/log', [FaxLogController::class, 'index'])->name('faxes.log.index');

    // Domain Groups
    Route::get('domain-groups', [DomainGroupsController::class, 'index'])->name('domain-groups.index');

    // Ring Groups
    Route::get('ring-groups', [RingGroupsController::class, 'index'])->name('ring-groups.index');

    // Gateways
    Route::get('gateways', [GatewayController::class, 'index'])->name('gateways.index');

    // Access Controls
    Route::get('access-controls', [AccessControlController::class, 'index'])->name('access-controls.index');

    // Dialplans
    Route::get('dialplans', [DialplanController::class, 'index'])->name('dialplans.index');

    // Recordings Manager
    Route::get('recordings-manager', [RecordingsManagerController::class, 'index'])->name('recordings-manager.index');
    Route::get('recordings-manager/{recording}/download', [RecordingsManagerController::class, 'download'])->name('recordings-manager.download');

    // Music on Hold
    Route::get('streams', [\App\Http\Controllers\StreamController::class, 'index'])->name('streams.index');
    Route::get('music-on-hold', [MusicOnHoldController::class, 'index'])->name('music-on-hold.index');
    Route::get('music-on-hold/{music_on_hold}/files/{file}', [MusicOnHoldController::class, 'download'])
        ->where('file', '[^/]+')
        ->name('music-on-hold.files.download');

    // Email Templates
    Route::get('email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');

    // Legacy Provisioning Templates
    Route::get('legacy-provision-templates', [LegacyProvisionTemplateController::class, 'index'])
        ->name('legacy-provision-templates.index');

    // Modules
    Route::get('modules', [SwitchModuleController::class, 'index'])->name('modules.index');

    // SIP Status
    Route::get('sip-status', [SipStatusController::class, 'index'])->name('sip-status.index');

    // SIP Profiles
    Route::get('sip-profiles', [SipProfileController::class, 'index'])->name('sip-profiles.index');

    // User Logs
    Route::get('user-logs', [UserLogsController::class, 'index'])->name('user-logs.index');

    // Database Transactions
    Route::get('database-transactions', [DatabaseTransactionController::class, 'index'])->name('database-transactions.index');

    // Business hours
    Route::get('business-hours', [BusinessHoursController::class, 'index'])->name('business-hours.index');

    // Paging groups
    Route::get('paging-groups', [PagingGroupsController::class, 'index'])->name('paging-groups.index');

    // Bridges
    Route::get('bridges', [BridgeController::class, 'index'])->name('bridges.index');

    // PIN Numbers
    Route::get('pin-numbers', [PinNumberController::class, 'index'])->name('pin-numbers.index');
    Route::get('pin-numbers/export', [PinNumberController::class, 'export'])->name('pin-numbers.export');

    // Call Blocks
    Route::get('call-blocks', [CallBlockController::class, 'index'])->name('call-blocks.index');

    // Call Flows
    Route::get('call-flows', [CallFlowController::class, 'index'])->name('call-flows.index');
    Route::get('dynamic-routes', [DynamicRouteController::class, 'index'])->name('dynamic-routes.index');

    // Basic Queues
    Route::get('basic-queues', [BasicQueueController::class, 'index'])->name('basic-queues.index');
    Route::get('basic-queues/agent-status', [BasicQueueController::class, 'agentStatus'])->name('basic-queues.agents.status');
    Route::get('active-basic-queues', [BasicQueueController::class, 'activeBasicQueues'])->name('active-basic-queues.index');

    // Advanced Contact Center & Supervisor Command Center
    Route::get('contact-center/supervisor-command-center', [\App\Http\Controllers\ContactCenter\SupervisorCommandCenterController::class, 'index'])->name('contact_center.supervisor_command_center');
    Route::get('api/contact-center/live-wallboard', [\App\Http\Controllers\ContactCenter\SupervisorCommandCenterController::class, 'liveWallboard'])->name('contact_center.live_wallboard');
    Route::post('api/contact-center/supervisor/action', [\App\Http\Controllers\ContactCenter\SupervisorCommandCenterController::class, 'executeAction'])->name('contact_center.supervisor.action');
    Route::post('api/contact-center/agent/status', [\App\Http\Controllers\ContactCenter\SupervisorCommandCenterController::class, 'setAgentStatus'])->name('contact_center.agent.set_status');
    Route::get('api/contact-center/agent/{agentUuid}/timeline', [\App\Http\Controllers\ContactCenter\SupervisorCommandCenterController::class, 'getAgentTimeline'])->name('contact_center.agent.timeline');
    Route::get('api/contact-center/supervisor/audit-logs', [\App\Http\Controllers\ContactCenter\SupervisorCommandCenterController::class, 'auditLogs'])->name('contact_center.supervisor.audit_logs');

    // Advanced Contact Center - Agent Desktop & Omnichannel Workspace
    Route::get('contact-center/agent-desktop', [\App\Http\Controllers\ContactCenter\AgentDesktopController::class, 'index'])->name('contact_center.agent_desktop');
    Route::get('api/contact-center/agent-desktop/status', [\App\Http\Controllers\ContactCenter\AgentDesktopController::class, 'status'])->name('contact_center.agent_desktop.status');
    Route::post('api/contact-center/agent-desktop/disposition', [\App\Http\Controllers\ContactCenter\AgentDesktopController::class, 'submitDisposition'])->name('contact_center.agent_desktop.disposition');
    Route::get('api/contact-center/customer/lookup', [\App\Http\Controllers\ContactCenter\AgentDesktopController::class, 'customerLookup'])->name('contact_center.customer.lookup');

    // Advanced Contact Center - QA Evaluation & Conversation Speech Intelligence
    Route::get('contact-center/qa-evaluation', [\App\Http\Controllers\ContactCenter\QaEvaluationController::class, 'index'])->name('contact_center.qa_evaluation');
    Route::post('api/contact-center/qa/evaluate', [\App\Http\Controllers\ContactCenter\QaEvaluationController::class, 'evaluate'])->name('contact_center.qa.evaluate');
    Route::post('api/contact-center/qa/acknowledge', [\App\Http\Controllers\ContactCenter\QaEvaluationController::class, 'acknowledge'])->name('contact_center.qa.acknowledge');
    Route::get('api/contact-center/speech-intelligence/{xmlCdrUuid}', [\App\Http\Controllers\ContactCenter\QaEvaluationController::class, 'getSpeechIntelligence'])->name('contact_center.speech_intelligence');

    // Advanced Contact Center - Workforce Management (WFM) & Adherence
    Route::get('contact-center/workforce-management', [\App\Http\Controllers\ContactCenter\WorkforceManagementController::class, 'index'])->name('contact_center.wfm');
    Route::get('api/contact-center/wfm/roster', [\App\Http\Controllers\ContactCenter\WorkforceManagementController::class, 'getRoster'])->name('contact_center.wfm.roster');
    Route::post('api/contact-center/wfm/save-schedule', [\App\Http\Controllers\ContactCenter\WorkforceManagementController::class, 'saveSchedule'])->name('contact_center.wfm.save_schedule');
    Route::post('api/contact-center/wfm/calculate-staffing', [\App\Http\Controllers\ContactCenter\WorkforceManagementController::class, 'calculateStaffing'])->name('contact_center.wfm.calculate_staffing');
    Route::get('api/contact-center/wfm/live-adherence', [\App\Http\Controllers\ContactCenter\WorkforceManagementController::class, 'liveAdherence'])->name('contact_center.wfm.live_adherence');

    // Advanced Contact Center - Outbound Campaign & Predictive Dialer Studio
    Route::get('contact-center/campaigns', [\App\Http\Controllers\ContactCenter\CampaignStudioController::class, 'index'])->name('contact_center.campaigns');
    Route::post('api/contact-center/campaigns', [\App\Http\Controllers\ContactCenter\CampaignStudioController::class, 'store'])->name('contact_center.campaigns.store');
    Route::post('api/contact-center/campaigns/{campaign}/start', [\App\Http\Controllers\ContactCenter\CampaignStudioController::class, 'start'])->name('contact_center.campaigns.start');
    Route::post('api/contact-center/campaigns/{campaign}/pause', [\App\Http\Controllers\ContactCenter\CampaignStudioController::class, 'pause'])->name('contact_center.campaigns.pause');
    Route::post('api/contact-center/campaigns/{campaign}/stop', [\App\Http\Controllers\ContactCenter\CampaignStudioController::class, 'stop'])->name('contact_center.campaigns.stop');
    Route::post('api/contact-center/campaigns/{campaign}/leads', [\App\Http\Controllers\ContactCenter\CampaignStudioController::class, 'addLeads'])->name('contact_center.campaigns.leads');
    Route::get('api/contact-center/campaigns/{campaign}/telemetry', [\App\Http\Controllers\ContactCenter\CampaignStudioController::class, 'telemetry'])->name('contact_center.campaigns.telemetry');

    // Advanced Contact Center - Skills-Based Routing & ACD Intelligence
    Route::get('contact-center/routing', [\App\Http\Controllers\ContactCenter\AcdRoutingController::class, 'index'])->name('contact_center.routing');
    Route::post('api/contact-center/routing/skills', [\App\Http\Controllers\ContactCenter\AcdRoutingController::class, 'saveSkill'])->name('contact_center.routing.skills.save');
    Route::delete('api/contact-center/routing/skills/{skill}', [\App\Http\Controllers\ContactCenter\AcdRoutingController::class, 'deleteSkill'])->name('contact_center.routing.skills.delete');
    Route::post('api/contact-center/routing/agent-skills', [\App\Http\Controllers\ContactCenter\AcdRoutingController::class, 'updateAgentSkills'])->name('contact_center.routing.agent_skills.update');
    Route::post('api/contact-center/routing/queue-policy', [\App\Http\Controllers\ContactCenter\AcdRoutingController::class, 'saveQueuePolicy'])->name('contact_center.routing.queue_policy.save');
    Route::post('api/contact-center/routing/callbacks/{callback}/action', [\App\Http\Controllers\ContactCenter\AcdRoutingController::class, 'callbackAction'])->name('contact_center.routing.callbacks.action');

    // Advanced Contact Center - SLA Intelligence & Analytics
    Route::get('contact-center/analytics', [\App\Http\Controllers\ContactCenter\AnalyticsController::class, 'index'])->name('contact_center.analytics');
    Route::get('contact-center/analytics/export', [\App\Http\Controllers\ContactCenter\AnalyticsController::class, 'export'])->name('contact_center.analytics.export');

    // Advanced Contact Center - Customer 360 & Ticket Workspace
    Route::get('contact-center/customer-360', [\App\Http\Controllers\ContactCenter\Customer360Controller::class, 'index'])->name('contact_center.customer360');
    Route::get('api/contact-center/customer-360/screen-pop', [\App\Http\Controllers\ContactCenter\Customer360Controller::class, 'screenPop'])->name('contact_center.customer360.screen_pop');
    Route::get('api/contact-center/customer-360/{customer}', [\App\Http\Controllers\ContactCenter\Customer360Controller::class, 'show'])->name('contact_center.customer360.show');
    Route::post('api/contact-center/customer-360', [\App\Http\Controllers\ContactCenter\Customer360Controller::class, 'store'])->name('contact_center.customer360.store');
    Route::patch('api/contact-center/customer-360/{customer}', [\App\Http\Controllers\ContactCenter\Customer360Controller::class, 'update'])->name('contact_center.customer360.update');
    Route::post('api/contact-center/customer-360/tickets', [\App\Http\Controllers\ContactCenter\Customer360Controller::class, 'storeTicket'])->name('contact_center.customer360.tickets.store');
    Route::patch('api/contact-center/customer-360/tickets/{ticket}', [\App\Http\Controllers\ContactCenter\Customer360Controller::class, 'updateTicket'])->name('contact_center.customer360.tickets.update');
    Route::post('api/contact-center/customer-360/interactions', [\App\Http\Controllers\ContactCenter\Customer360Controller::class, 'logInteraction'])->name('contact_center.customer360.interactions.store');

    // Advanced Contact Center - Omnichannel Messaging Hub
    Route::get('contact-center/omnichannel', [\App\Http\Controllers\ContactCenter\OmnichannelController::class, 'index'])->name('contact_center.omnichannel');
    Route::get('api/contact-center/omnichannel/{conversation}', [\App\Http\Controllers\ContactCenter\OmnichannelController::class, 'show'])->name('contact_center.omnichannel.show');
    Route::post('api/contact-center/omnichannel/{conversation}/reply', [\App\Http\Controllers\ContactCenter\OmnichannelController::class, 'sendMessage'])->name('contact_center.omnichannel.reply');
    Route::post('api/contact-center/omnichannel/{conversation}/assign', [\App\Http\Controllers\ContactCenter\OmnichannelController::class, 'assign'])->name('contact_center.omnichannel.assign');
    Route::post('api/contact-center/omnichannel/{conversation}/status', [\App\Http\Controllers\ContactCenter\OmnichannelController::class, 'updateStatus'])->name('contact_center.omnichannel.status');
    Route::post('api/contact-center/omnichannel/simulate', [\App\Http\Controllers\ContactCenter\OmnichannelController::class, 'simulateInbound'])->name('contact_center.omnichannel.simulate');

    // Advanced Contact Center - Inbound IVR Journey Studio
    Route::get('contact-center/ivr-studio', [\App\Http\Controllers\ContactCenter\IvrStudioController::class, 'index'])->name('contact_center.ivr_studio');
    Route::post('api/contact-center/ivr-studio', [\App\Http\Controllers\ContactCenter\IvrStudioController::class, 'store'])->name('contact_center.ivr_studio.store');
    Route::patch('api/contact-center/ivr-studio/{flow}', [\App\Http\Controllers\ContactCenter\IvrStudioController::class, 'update'])->name('contact_center.ivr_studio.update');
    Route::post('api/contact-center/ivr-studio/{flow}/simulate', [\App\Http\Controllers\ContactCenter\IvrStudioController::class, 'simulate'])->name('contact_center.ivr_studio.simulate');
    Route::get('api/contact-center/ivr-studio/{flow}/xml', [\App\Http\Controllers\ContactCenter\IvrStudioController::class, 'exportXml'])->name('contact_center.ivr_studio.xml');

    // Telecom Intelligence - Carrier Trunks, LCR & Routing Policies (P2)
    Route::get('telecom/routing-intelligence', [\App\Http\Controllers\Telecom\RoutingIntelligenceController::class, 'index'])->name('telecom.routing_intelligence');
    Route::post('api/telecom/routing-intelligence/simulate', [\App\Http\Controllers\Telecom\RoutingIntelligenceController::class, 'simulate'])->name('telecom.routing_intelligence.simulate');
    Route::post('api/telecom/routing-intelligence/carriers/{carrier}/circuit-breaker', [\App\Http\Controllers\Telecom\RoutingIntelligenceController::class, 'toggleCircuitBreaker'])->name('telecom.routing_intelligence.circuit_breaker');

    // Financial Rating, Double-Entry Ledger & Billing Studio (P3)
    Route::get('billing/financial-studio', [\App\Http\Controllers\Billing\FinancialStudioController::class, 'index'])->name('billing.financial_studio');
    Route::post('api/billing/financial-studio/simulate', [\App\Http\Controllers\Billing\FinancialStudioController::class, 'simulate'])->name('billing.financial_studio.simulate');
    Route::post('api/billing/financial-studio/deposit', [\App\Http\Controllers\Billing\FinancialStudioController::class, 'deposit'])->name('billing.financial_studio.deposit');
    Route::post('api/billing/financial-studio/invoice', [\App\Http\Controllers\Billing\FinancialStudioController::class, 'generateInvoice'])->name('billing.financial_studio.invoice');

    // PBX Advanced Operations, Fleet Governance & Emergency Routing (P4)
    Route::get('pbx/advanced-operations', [\App\Http\Controllers\Pbx\AdvancedOperationsController::class, 'index'])->name('pbx.advanced_operations');
    Route::post('api/pbx/advanced-operations/emergency-dispatch', [\App\Http\Controllers\Pbx\AdvancedOperationsController::class, 'testEmergencyDispatch'])->name('pbx.advanced_operations.emergency_dispatch');
    Route::post('api/pbx/advanced-operations/caller-id-simulate', [\App\Http\Controllers\Pbx\AdvancedOperationsController::class, 'simulateCallerId'])->name('pbx.advanced_operations.caller_id_simulate');
    Route::post('api/pbx/advanced-operations/permission-evaluate', [\App\Http\Controllers\Pbx\AdvancedOperationsController::class, 'evaluateCallPermission'])->name('pbx.advanced_operations.permission_evaluate');
    Route::post('api/pbx/advanced-operations/legal-holds/{holdUuid}/toggle', [\App\Http\Controllers\Pbx\AdvancedOperationsController::class, 'toggleLegalHold'])->name('pbx.advanced_operations.legal_hold_toggle');
    Route::post('api/pbx/advanced-operations/devices/{deviceUuid}/reboot', [\App\Http\Controllers\Pbx\AdvancedOperationsController::class, 'rebootDevice'])->name('pbx.advanced_operations.device_reboot');

    // Carrier Infrastructure, Multi-Node FreeSWITCH Topology & Platform Resilience (P6)
    Route::get('carrier/infrastructure', [\App\Http\Controllers\Carrier\InfrastructureController::class, 'index'])->name('carrier.infrastructure');
    Route::post('api/carrier/infrastructure/nodes/{nodeUuid}/drain', [\App\Http\Controllers\Carrier\InfrastructureController::class, 'toggleDrain'])->name('carrier.infrastructure.node_drain');
    Route::post('api/carrier/infrastructure/nodes/{nodeUuid}/ping', [\App\Http\Controllers\Carrier\InfrastructureController::class, 'pingNode'])->name('carrier.infrastructure.node_ping');
    Route::post('api/carrier/infrastructure/drills/simulate-failover', [\App\Http\Controllers\Carrier\InfrastructureController::class, 'simulateFailover'])->name('carrier.infrastructure.simulate_failover');
    Route::post('api/carrier/infrastructure/snapshots/create', [\App\Http\Controllers\Carrier\InfrastructureController::class, 'createSnapshot'])->name('carrier.infrastructure.create_snapshot');

    // SMS, Voice SMS & SMPP Gateway Studio (P7)
    Route::get('messaging/sms-voice-studio', [\App\Http\Controllers\Messaging\SmsVoiceStudioController::class, 'index'])->name('messaging.sms_voice_studio');
    Route::post('api/messaging/sms-voice-studio/send', [\App\Http\Controllers\Messaging\SmsVoiceStudioController::class, 'send'])->name('messaging.sms_voice_studio.send');
    Route::post('api/messaging/sms-voice-studio/calculate-segments', [\App\Http\Controllers\Messaging\SmsVoiceStudioController::class, 'calculateSegments'])->name('messaging.sms_voice_studio.calculate_segments');
    Route::post('api/messaging/sms-voice-studio/connectors/{connectorUuid}/toggle-bind', [\App\Http\Controllers\Messaging\SmsVoiceStudioController::class, 'toggleBind'])->name('messaging.sms_voice_studio.toggle_bind');
    Route::post('api/messaging/sms-voice-studio/dnd', [\App\Http\Controllers\Messaging\SmsVoiceStudioController::class, 'addDnd'])->name('messaging.sms_voice_studio.add_dnd');
    Route::delete('api/messaging/sms-voice-studio/dnd/{suppressionUuid}', [\App\Http\Controllers\Messaging\SmsVoiceStudioController::class, 'removeDnd'])->name('messaging.sms_voice_studio.remove_dnd');

    // AI Voice & Autonomous Automation Platform (P8)
    Route::get('ai/voice-automation', [\App\Http\Controllers\Ai\VoiceAutomationController::class, 'index'])->name('ai.voice_automation');
    Route::post('api/ai/voice-automation/simulate-turn', [\App\Http\Controllers\Ai\VoiceAutomationController::class, 'simulateTurn'])->name('ai.voice_automation.simulate_turn');
    Route::post('api/ai/voice-automation/search-knowledge', [\App\Http\Controllers\Ai\VoiceAutomationController::class, 'searchKnowledge'])->name('ai.voice_automation.search_knowledge');
    Route::post('api/ai/voice-automation/agents/{agentUuid}/prompt', [\App\Http\Controllers\Ai\VoiceAutomationController::class, 'updatePrompt'])->name('ai.voice_automation.update_prompt');

    // API, Integration Ecosystem & Developer Portal (P9)
    Route::get('api-ecosystem/developer-portal', [\App\Http\Controllers\Api\DeveloperPortalController::class, 'index'])->name('api_ecosystem.developer_portal');
    Route::post('api/ecosystem/credentials', [\App\Http\Controllers\Api\DeveloperPortalController::class, 'storeKey'])->name('api_ecosystem.credentials.store');
    Route::post('api/ecosystem/credentials/{uuid}/revoke', [\App\Http\Controllers\Api\DeveloperPortalController::class, 'revokeKey'])->name('api_ecosystem.credentials.revoke');
    Route::post('api/ecosystem/credentials/{uuid}/rotate', [\App\Http\Controllers\Api\DeveloperPortalController::class, 'rotateKeySecret'])->name('api_ecosystem.credentials.rotate');
    Route::post('api/ecosystem/webhooks', [\App\Http\Controllers\Api\DeveloperPortalController::class, 'storeWebhook'])->name('api_ecosystem.webhooks.store');
    Route::delete('api/ecosystem/webhooks/{uuid}', [\App\Http\Controllers\Api\DeveloperPortalController::class, 'deleteWebhook'])->name('api_ecosystem.webhooks.delete');
    Route::post('api/ecosystem/webhooks/{uuid}/test', [\App\Http\Controllers\Api\DeveloperPortalController::class, 'testWebhook'])->name('api_ecosystem.webhooks.test');

    // Security, Fraud Detection & Regulatory Compliance Studio (P10)
    Route::get('security/fraud-compliance-studio', [\App\Http\Controllers\Security\FraudComplianceController::class, 'index'])->name('security.fraud_compliance_studio');
    Route::post('api/security/fraud-rules', [\App\Http\Controllers\Security\FraudComplianceController::class, 'storeRule'])->name('security.fraud_rules.store');
    Route::post('api/security/fraud-rules/{uuid}/toggle', [\App\Http\Controllers\Security\FraudComplianceController::class, 'toggleRule'])->name('security.fraud_rules.toggle');
    Route::post('api/security/ip-quarantine', [\App\Http\Controllers\Security\FraudComplianceController::class, 'quarantineIp'])->name('security.ip_quarantine.store');
    Route::post('api/security/ip-quarantine/{uuid}/unquarantine', [\App\Http\Controllers\Security\FraudComplianceController::class, 'unquarantineIp'])->name('security.ip_quarantine.unquarantine');
    Route::post('api/security/simulate-threat-evaluation', [\App\Http\Controllers\Security\FraudComplianceController::class, 'simulateThreat'])->name('security.simulate_threat');
    Route::post('api/security/incidents/{uuid}/resolve', [\App\Http\Controllers\Security\FraudComplianceController::class, 'resolveIncident'])->name('security.incidents.resolve');
    Route::post('api/security/compliance-policy', [\App\Http\Controllers\Security\FraudComplianceController::class, 'updatePolicy'])->name('security.compliance_policy.update');

    // Operations Intelligence & Cross-Domain Analytics Studio (P11)
    Route::get('analytics/operations-intelligence', [\App\Http\Controllers\Analytics\OperationsIntelligenceController::class, 'index'])->name('analytics.operations_intelligence');
    Route::post('api/analytics/operations-intelligence/export-report', [\App\Http\Controllers\Analytics\OperationsIntelligenceController::class, 'storeExportJob'])->name('analytics.operations_intelligence.export');
    Route::post('api/analytics/operations-intelligence/save-view', [\App\Http\Controllers\Analytics\OperationsIntelligenceController::class, 'storeSavedView'])->name('analytics.operations_intelligence.save_view');
    Route::post('api/analytics/operations-intelligence/forecast-capacity', [\App\Http\Controllers\Analytics\OperationsIntelligenceController::class, 'runCapacityForecast'])->name('analytics.operations_intelligence.forecast');

    // Release Engineering & Production Readiness Studio (P12)
    Route::get('system/release-readiness', [\App\Http\Controllers\System\ReleaseReadinessController::class, 'index'])->name('system.release_readiness');
    Route::get('api/system/release-readiness/audit-check', [\App\Http\Controllers\System\ReleaseReadinessController::class, 'auditCheck'])->name('system.release_readiness.audit_check');
    Route::post('api/system/release-readiness/trigger-backup', [\App\Http\Controllers\System\ReleaseReadinessController::class, 'triggerBackup'])->name('system.release_readiness.trigger_backup');
    Route::post('api/system/release-readiness/flush-cache-warmup', [\App\Http\Controllers\System\ReleaseReadinessController::class, 'flushCacheWarmup'])->name('system.release_readiness.flush_cache_warmup');
    Route::post('api/system/release-readiness/simulate-workflows', [\App\Http\Controllers\System\ReleaseReadinessController::class, 'simulateWorkflows'])->name('system.release_readiness.simulate_workflows');

    // Basic Dialer
    Route::get('basic-dialer', [BasicDialerController::class, 'index'])->name('basic-dialer.index');

    // Conference Centers
    Route::get('conference-centers', [ConferenceCenterController::class, 'index'])->name('conference-centers.index');
    Route::get('ai-agents', [AiAgentController::class, 'index'])->name('ai-agents.index');
    Route::get('conferences', [ConferenceController::class, 'index'])->name('conferences.index');
    Route::get('conference-controls', [ConferenceControlController::class, 'index'])->name('conference-controls.index');
    Route::get('conference-profiles', [ConferenceProfileController::class, 'index'])->name('conference-profiles.index');
    Route::get('conference-rooms', [ConferenceRoomController::class, 'index'])->name('conference-rooms.index');

    //Voicemails
    Route::get('voicemails', [VoicemailController::class, 'index'])->name('voicemails.index');

    // Voicemail Messages
    Route::get('/voicemails/{voicemail}/messages/', [VoicemailMessagesController::class, 'index'])->name('voicemails.messages.index');

    // Virtual Receptionist
    Route::get('virtual-receptionists', [VirtualReceptionistController::class, 'index'])->name('virtual-receptionists.index');

    // Account settings
    Route::get('account-settings', [AccountSettingsController::class, 'index'])->name('account-settings.index');

    // Default settings
    Route::get('default-settings', [\App\Http\Controllers\DefaultSettingsController::class, 'index'])->name('default-settings.index');

    // Domain settings
    Route::get('domains/{domain}/settings', [\App\Http\Controllers\DomainSettingsController::class, 'index'])->name('domains.settings.index');

    // Logs
    Route::get('logs', [LogsController::class, 'index'])->name('logs.index');

    // System Settings
    Route::get('system-settings', [SystemSettingsController::class, 'index'])->name('system-settings.index');

    // System
    Route::get('system', [SystemController::class, 'index'])->name('system.index');

    // Switch Variables
    Route::get('vars', [SwitchVariableController::class, 'index'])->name('switch-variables.index');

    // Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout']);

    //Devices
    Route::get('devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('devices/duplicate', [DeviceController::class, 'duplicate'])->name('devices.duplicate');
    Route::get('/devices/template/download', [DeviceImportExportController::class, 'downloadTemplate'])->name('devices.template.download');
    Route::post('/devices/import', [DeviceImportExportController::class, 'importPreview'])->name('devices.import.preview');
    Route::post('/devices/import/commit', [DeviceImportExportController::class, 'importCommit'])->name('devices.import.commit');
    Route::get('device-key-templates', [DeviceKeyTemplateController::class, 'index'])->name('device-key-templates.index');
    Route::get('device-profiles', [DeviceProfileController::class, 'index'])->name('device-profiles.index');

    //Phonebooks
    Route::get('phonebooks', [PhonebookManagerController::class, 'index'])->name('phonebooks.index');

    //Phone Numbers
    Route::get('phone-numbers', [PhoneNumbersController::class, 'index'])->name('phone-numbers.index');
    Route::get('/phone-numbers-export', [PhoneNumbersController::class, 'export'])->name('phone-numbers.export');
    Route::post('/phone-numbers/import', [PhoneNumbersController::class, 'importPreview'])->name('phone-numbers.import.preview');
    Route::post('/phone-numbers/import/commit', [PhoneNumbersController::class, 'importCommit'])->name('phone-numbers.import.commit');

    //Wakeup Calls
    Route::get('wakeup-calls', [WakeupCallsController::class, 'index'])->name('wakeup-calls.index');

    // Scheduled Announcements
    Route::get('scheduled-announcements', [ScheduledAnnouncementController::class, 'index'])->name('scheduled-announcements.index');


    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //Users
    // Route::get('/users', [UserController::class, 'index']) ->name('usersList');
    //Route::get('/users/create', [UserController::class, 'createUser']) ->name('usersCreateUser');
    //Route::get('/users/edit/{id}', [UserController::class, 'editUser']) ->name('editUser');
    // Route::post('/saveUser', [UserController::class, 'saveUser']) ->name('saveUser');
    // Route::post('/updateUser', [UserController::class, 'updateUser']) ->name('updateUser');


    //Voicemails
    Route::post('/voicemails/greetings/upload/{voicemail}', [VoicemailController::class, 'uploadVoicemailGreeting'])->name('uploadVoicemailGreeting');
    Route::get('/voicemails/{voicemail}/greetings/{filename}', [VoicemailController::class, 'getVoicemailGreeting'])->name('getVoicemailGreeting');
    Route::get('/voicemails/{voicemail}/greetings/{filename}/download', [VoicemailController::class, 'downloadVoicemailGreeting'])->name('downloadVoicemailGreeting');
    Route::get('/voicemails/{voicemail}/greetings/{filename}/delete', [VoicemailController::class, 'deleteVoicemailGreeting'])->name('deleteVoicemailGreeting');

    //Apps
    Route::resource('apps', AppsController::class);
    Route::post('/apps/mobile-app-options', [AppsController::class, 'getMobileAppOptions'])->name('apps.user.options');
    //Route::get('/apps/organization/update', [AppsController::class, 'updateOrganization']) ->name('appsUpdateOrganization');
    Route::get('/apps/connection/update', [AppsController::class, 'updateConnection'])->name('appsUpdateConnection');
    Route::post('/apps/user/create', [AppsController::class, 'createUser'])->name('apps.user.create');
    Route::post('/apps/user/delete', [AppsController::class, 'deleteUser'])->name('apps.user.delete');
    Route::post('/apps/user/activate', [AppsController::class, 'activateUser'])->name('apps.user.activate');
    Route::post('/apps/user/deactivate', [AppsController::class, 'deactivateUser'])->name('apps.user.deactivate');
    Route::post('/apps/user/state', [AppsController::class, 'setUserState'])->name('apps.user.state');
    Route::post('/apps/user/device/delete', [AppsController::class, 'deleteDevice'])->name('apps.user.device.delete');
    Route::post('/apps/user/reset-password', [AppsController::class, 'resetPassword'])->name('apps.user.reset');
    Route::post('/apps/users/{extension}/status', [AppsController::class, 'SetStatus'])->name('appsSetStatus');
    Route::get('/apps/email', [AppsController::class, 'emailUser'])->name('emailUser');

    // Speed Dial
    Route::get('speed-dial', [SpeedDialController::class, 'index'])->name('speed-dial.index');

    // SMS for testing
    // Route::get('/sms/ringotelwebhook', [SmsWebhookController::class,"messageFromRingotel"]);

    // Messages
    // Route::resource('messages', MessagesController::class);
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    // Route::post('/messages/bulk-update', [DeviceController::class, 'bulkUpdate'])->name('messages.bulk.update');
    // Route::post('/messages/bulk-delete', [DeviceController::class, 'bulkDelete'])->name('messages.bulk.delete');
    // Route::post('/messages/select-all', [DeviceController::class, 'selectAll'])->name('messages.select.all');



    // Message Settings
    Route::get('/message-settings', [MessageSettingsController::class, 'index'])->name('messages.settings');


    // Firewall
    Route::get('firewall', [FirewallController::class, 'index'])->name('firewall.index');



    // Fax Queue

    // Recordings
    Route::get('recordings', [RecordingsController::class, 'index'])->name('recordings.index');
    Route::get('recordings/i-{recording}', [RecordingsController::class, 'show'])->name('recordings.show');
    Route::get('recordings/{filename}', [RecordingsController::class, 'file'])->name('recordings.file');
    Route::delete('recordings/{recording}', [RecordingsController::class, 'destroy'])->name('recordings.destroy');
    Route::post('recordings', [RecordingsController::class, 'store'])->name('recordings.store');
    Route::post('recordings/storeBlob', [RecordingsController::class, 'storeBlob'])->name('recordings.storeBlob');
    Route::put('recordings/{recording}', [RecordingsController::class, 'update'])->name('recordings.update');
    Route::put('recordings/{recording}/{entity}/{entityid}', [RecordingsController::class, 'use'])->name('recordings.use');

    //Route::get('/recordings/{filename?}', [RecordingsController::class, 'getRecordings']) ->name('getRecordings');

    // Activity Log
    Route::get('activities', [ActivityLogController::class, 'index'])->name('activities.index');

    // Reports
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');

    // Call Routing options
    Route::post('/call-routing-options', [CallRoutingOptionsController::class, 'getRoutingOptions'])->name('routing.options');

    // Registrations
    Route::get('registrations', [RegistrationsController::class, 'index'])->name('registrations.index');

    // Sansay Registrations
    Route::get('sansay/registrations', [SansayRegistrationsController::class, 'index'])->name('sansay.registrations.index');

    // Sansay Active Calls
    Route::get('sansay/active-calls', [SansayActiveCallsController::class, 'index'])->name('sansay.active-calls.index');

    // Active Calls
    Route::get('active-conferences', [ActiveConferenceController::class, 'index'])->name('active-conferences.index');
    Route::get('active-conferences/{conference}/interactive', [ActiveConferenceController::class, 'interactive'])->name('active-conferences.interactive');
    Route::get('active-calls', [ActiveCallsController::class, 'index'])->name('active-calls.index');

    // Pro Features
    Route::get('pro-features', [ProFeaturesController::class, 'index'])->name('pro-features.index');


    // Cloud Provisioning
    //Route::resource('cloud-provisioning', DeviceCloudProvisioningController::class);
    Route::post('/cloud-provisioning/domains', [DeviceCloudProvisioningController::class, 'getAvailableDomains'])->name('cloud-provisioning.domains');
    // Route::post('/cloud-provisioning/select-all', [ActiveCallsController::class, 'selectAll'])->name('active-calls.select.all');
    // Route::post('/cloud-provisioning/action', [ActiveCallsController::class, 'handleAction'])->name('active-calls.action');
    Route::post('/cloud-provisioning/sync-devices', [DeviceCloudProvisioningController::class, 'syncDevices'])->name('cloud-provisioning.devices.sync');
    Route::post('/cloud-provisioning/register', [DeviceCloudProvisioningController::class, 'register'])->name('cloud-provisioning.register');
    Route::post('/cloud-provisioning/deregister', [DeviceCloudProvisioningController::class, 'deregister'])->name('cloud-provisioning.deregister');
    //Route::post('/cloud-provisioning/devices/organizations', [DeviceCloudProvisioningController::class, 'devicesOrganizations'])->name('cloudProvisioning.devices.organizations');
    //Route::post('/cloud-provisioning/devices/organizations', [DeviceCloudProvisioningController::class, 'devicesOrganizations'])->name('cloudProvisioning.devices.organizations');
    //Route::post('/cloud-provisioning/devices/organizations', [DeviceCloudProvisioningController::class, 'devicesOrganizations'])->name('cloudProvisioning.devices.organizations');


    // Whitelisted Numbers
    Route::get('whitelisted-numbers', [WhitelistedNumbersController::class, 'index'])->name('whitelisted-numbers.index');
});


// Route::group(['prefix' => '/'], function () {
//     Route::get('', [RoutingController::class, 'index'])->name('root');
//     Route::get('{first}/{second}/{third}', [RoutingController::class, 'thirdLevel'])->name('third');
//     Route::get('{first}/{second}', [RoutingController::class, 'secondLevel'])->name('second');
//     Route::get('/any/{any}', [RoutingController::class, 'root'])->name('any');
// });

// Auth::routes();

//Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
