import React , {forwardRef} from "react";
import {CheckBoxProps} from "../../types";
import {Info} from "@phosphor-icons/react";
import {Tooltip} from "./index";

const Checkbox: forwardRef<CheckBoxProps> = (({label, tooltip, className = "", ...props}, ref) => {
    return (
        <label className="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-white">
            <input
                ref={ref}
                type="checkbox"
                className={`rounded border-gray-300 focus:ring-blue-500 ${className}`}
                {...props}
            />
            {label && <span>{label}</span>}
            {tooltip && (
                <div className="flex items-center gap-1">
                    <Tooltip content={tooltip}>
                        <Info/>
                    </Tooltip>
                </div>
            )}
        </label>
    );
});

export default Checkbox;