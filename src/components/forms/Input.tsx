// components/Form/Input.tsx
import React, {forwardRef} from "react";
import {InputProps} from "../../types";
import {Info} from "@phosphor-icons/react";
import {Tooltip} from "./index";
import {__} from "@wordpress/i18n";


const Input: forwardRef<InputProps> = (({label, tooltip, type = "text", className = "", ...rest}, ref) => {
    return (
        <label htmlFor={rest.id}
               className="flex flex-col gap-1 text-sm font-medium text-gray-700 relative dark:text-white w-[100%]">
            <div className="flex items-center gap-1">
                {label && <span>{__(label, 'brutefort')}</span>}
                {tooltip && (
                    <Tooltip content={__(tooltip, 'brutefort')}>
                        <Info size={14}/>
                    </Tooltip>
                )}
            </div>
            <input
                ref={ref}
                type={type}
                className={`rounded-md border border-gray-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 ${className}`}
                {...rest}
            />
        </label>
    );
});

export default Input;
