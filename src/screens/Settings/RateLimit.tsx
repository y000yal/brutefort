import React, {useState} from "react";
import {Input, CheckBox, Radio, Tooltip} from "../../components/forms";
import {Info} from "@phosphor-icons/react";

const RateLimit = () => {
    const [extendLockout, setExtendLockout] = useState(false);
    const handleLockoutExtension = (e) => {
        setExtendLockout(e.target.checked);
    }
    return (
        <div className="flex gap-4 justify-around flex-col">
            <div className="flex gap-3 flex-col">
                <div className="settings-title flex items-center gap-1">
                    <span className="font-medium text-sm text-gray-700 dark:text-white">Max Allowed Attempts</span>
                    <Tooltip content="e.g. default 3 attempts per 15 minutes">
                        <Info/>
                    </Tooltip>
                </div>
                <div className="settings-body flex items-center gap-2">
                    <Input
                        id="bf-max-attempts"
                        name="bf_max_attempts"
                        min={1}
                        defaultValue={3}
                        type="number"
                        className="w-[50px]"
                    />
                    <span className="italic">attempt(s)/</span>
                    <Input
                        id="bf-time-window"
                        name="bf_time_window"
                        min={1}
                        defaultValue={15}
                        type="number"
                        placeholder="in minutes..."
                        className="w-[80px]"
                    />
                    <span className="italic">minutes</span>
                </div>
            </div>
            <>
                <Input
                    id="bf-lockout-duration"
                    name="bf_lockout_duration"
                    min={1}
                    defaultValue={5}
                    type="number"
                    label="Lockout Duration"
                    placeholder="in minutes..."
                    tooltip="How long an IP is blocked after limit."
                />
            </>
            <>
                <CheckBox
                    id="bf-extend-lockout"
                    name="bg_extend_lockout"
                    defaultChecked={extendLockout}
                    label="Enable lockout extension"
                    onChange={handleLockoutExtension}
                    tooltip="Tick this if you want to extend the lockout period if the login attempt keeps on failing."
                />
            </>
            <>
                {extendLockout && (
                    <Input
                        id="bf-extend-duration"
                        name="bf_extend_duration"
                        min={1}
                        defaultValue={1}
                        type="number"
                        label="Extended Duration"
                        placeholder="in hours..."
                        tooltip="How long should the restriction time be extended in hours."
                    />
                )}
            </>
            <>
                <Input
                    id="bf-custom-error-message"
                    name="bf_custom_error_message"
                    defaultValue="Too many attempts!!."
                    type="text"
                    label="Custom Error Message"
                    placeholder="Too many attempts!!."
                    tooltip="Change the error message shown when limit is reached."
                />
            </>
        </div>
    )
};
export default RateLimit;

