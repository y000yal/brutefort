
import React from "react";
import {RadioProps} from "../../types";
import {Info} from "@phosphor-icons/react";
import { Tooltip } from "react-tooltip";


const Radio: React.FC<RadioProps> = ({ label, tooltip , className = "", ...props }) => {
    return (

        <label className="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
            <div className="flex items-center gap-1">
                {label && <span>{label}</span>}
                {tooltip && (
                      <>
                      <Tooltip id={props.id} place="bottom" content={tooltip} />
                      <Info data-tooltip-id={props.id} size={14} />
                    </>
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