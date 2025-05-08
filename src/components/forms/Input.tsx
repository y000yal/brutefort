// components/Form/Input.tsx
import React from "react";
import {InputProps} from "../../types";
import {Info} from "@phosphor-icons/react";
import {Tooltip} from "./index";


const Input: React.FC<InputProps> = ({label, tooltip, type = "text", className = "", ...props}) => {
    return (
        <label className="flex flex-col gap-1 text-sm font-medium text-gray-700 relative dark:text-white">
            <div className="flex items-center gap-1">
                {label && <span>{label}</span>}
                {tooltip && (
                    <Tooltip content={tooltip}>
                        <Info />
                    </Tooltip>
                )}
            </div>
            <input
                type={type}
                className={`rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 ${className}`}
                {...props}
            />
        </label>
    );
};

export default Input;
