-- ZneroPBX Native STIR/SHAKEN Lua Hook for FreeSWITCH Outbound Identity Injection
-- Invoked by Outbound Routes dialplan before carrier bridge

local api = freeswitch.API()

if (session:ready()) then
    local caller_id = session:getVariable("caller_id_number") or ""
    local destination = session:getVariable("destination_number") or ""
    local domain_uuid = session:getVariable("domain_uuid") or ""

    if (caller_id ~= "" and destination ~= "") then
        freeswitch.consoleLog("NOTICE", "[STIR/SHAKEN] Generating PASSporT for " .. caller_id .. " -> " .. destination .. "\n")

        -- Execute local artisan stir-shaken signing helper
        local cmd = "php /var/www/fspbx/artisan enterprise:stir-shaken-sign-call " ..
                    "--caller=" .. caller_id .. " --dest=" .. destination .. " --domain=" .. domain_uuid .. " --json"
        local output = api:execute("system", cmd)

        if (output and string.find(output, "Identity:")) then
            -- Extract Identity header
            local _, _, identity_header = string.find(output, "Identity:%s*(.-)\n")
            if (identity_header) then
                session:setVariable("sip_h_Identity", identity_header)
                freeswitch.consoleLog("NOTICE", "[STIR/SHAKEN] Successfully injected sip_h_Identity header.\n")
            end
        else
            freeswitch.consoleLog("WARNING", "[STIR/SHAKEN] Signing command did not return Identity header: " .. (output or "") .. "\n")
        end
    end
end
