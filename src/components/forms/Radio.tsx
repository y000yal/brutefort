
import React from "react";
import {RadioProps} from "../../types";
import {Info} from "@phosphor-icons/react";


const Radio: React.FC<RadioProps> = ({ label, tooltip , className = "", ...props }) => {
    return (

        <label className="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
            <div className="flex items-center gap-1">
                {label && <span>{label}</span>}
                {tooltip && (
                    <div className="group relative cursor-help">
                        <Info size={16} className="text-gray-400" />
                        <div className="absolute left-1/2 top-full z-10 w-max -translate-x-1/2 rounded bg-black px-2 py-1 text-xs text-white opacity-0 group-hover:opacity-100 group-hover:block transition-opacity">
                            {tooltip}
                        </div>
                    </div>
                )}
            </div>
            <input
                type="radio"
                className={`rounded-full border-gray-300 focus:ring-blue-500 ${className}`}
                {...props}
            />
            {label && <span>{label}</span>}
        </label>
    );
};

export default Radio;