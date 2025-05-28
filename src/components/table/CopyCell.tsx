import React, {useState} from "react";
import {CheckSquare, Copy} from "@phosphor-icons/react";
import {__} from "@wordpress/i18n";

type Props = {
    value: string | number;
};

const CopyCell: React.FC<Props> = ({value}) => {
    const [copied, setCopied] = React.useState(false);
    const [showTooltip, setShowTooltip] = useState(false);

    const handleCopy = async () => {
        try {
            await navigator.clipboard.writeText(String(value));
            setCopied(true);
            setShowTooltip(false); // Hide tooltip on copy
            setTimeout(() => setCopied(false), 500);
        } catch (err) {
            console.error("Copy failed", err);
        }
    };

    return (
        <div
            className="flex items-center gap-2 log-status log-id text-gray-500 capitalize  w-fit"
            onClick={handleCopy}
        >
            <div
                className="cursor-pointer relative"
                onMouseEnter={() => !copied && setShowTooltip(true)}
                onMouseLeave={() => setShowTooltip(false)}
            >
                {copied ? (
                    <CheckSquare size={16} weight="bold" className="text-green-500"/>
                ) : (
                    <Copy size={16} weight="bold"/>
                )}

                {showTooltip && (
                    <span
                        className="absolute top-5 -left-4 bg-black text-white text-xs rounded px-2 py-1  transition-opacity duration-150">
                {__("Copy", 'brutefort')}
            </span>
                )}
            </div>
            <span>{value}</span>
        </div>
    );
};

export default CopyCell;
