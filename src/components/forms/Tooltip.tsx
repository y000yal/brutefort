// components/Tooltip.tsx
import React, { useEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";

type TooltipProps = {
    content: string;
    children: React.ReactNode;
    bgColorClass?: string; // Tailwind class e.g. "bg-black" or "bg-blue-700"
};

const Tooltip: React.FC<TooltipProps> = ({
                                             content,
                                             children,
                                             bgColorClass = "bg-black",
                                         }) => {
    const [visible, setVisible] = useState(false);
    const [coords, setCoords] = useState({ top: 0, left: 0 });
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const el = ref.current;
        if (!el) return;

        const handleMouseEnter = () => {
            const rect = el.getBoundingClientRect();
            setCoords({
                top: rect.top + window.scrollY-2,
                left: rect.left + window.scrollX ,
            });
            setVisible(true);
        };

        const handleMouseLeave = () => setVisible(false);

        el.addEventListener("mouseenter", handleMouseEnter);
        el.addEventListener("mouseleave", handleMouseLeave);

        return () => {
            el.removeEventListener("mouseenter", handleMouseEnter);
            el.removeEventListener("mouseleave", handleMouseLeave);
        };
    }, []);

    return (
        <>
            <div ref={ref} className="inline-block relative">
                {children}
            </div>
            {visible &&
                createPortal(
                    <div
                        className={`fixed z-1000 px-3 py-3 text-xs text-white rounded shadow-md transition-opacity ${bgColorClass}`}
                        style={{
                            top: coords.top - 10,
                            left: coords.left,
                            transform: "translate(-50%, -100%)",
                        }}
                    >
                        {content}
                        {/* Bottom Arrow */}
                        <div
                            className={`absolute left-1/2 top-full -mt-0.5 h-3 w-3 rotate-90 ${bgColorClass}`}
                            style={{
                                transform: "translateX(-50%) rotate(45deg)"
                            }}
                        />
                    </div>,
                    document.body
                )}
        </>
    );
};

export default Tooltip;
