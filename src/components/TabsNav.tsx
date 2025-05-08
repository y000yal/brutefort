import React, {useEffect, useRef, useState} from "react";
import {NavLink, useLocation} from "react-router-dom";
import {TABS} from "../constants/tabs";

const TabsNav: React.FC = () => {
    const tabRefs = useRef<Record<string, HTMLAnchorElement | null>>({});
    const [pillStyle, setPillStyle] = useState<React.CSSProperties>({});
    const location = useLocation();
    // Detect active tab key based on path
    const activeTabKey = Object.entries(TABS).find(
        ([, {path}]) => '/' + path === (location.pathname.length > 1 ? location.pathname : '/general')
    )?.[0];

    useEffect(() => {
        if (!activeTabKey) return;

        const updatePill = () => {
            const el = tabRefs.current[activeTabKey];

            if (el) {
                const rect = el.getBoundingClientRect();
                const parentRect = el.parentElement?.getBoundingClientRect();
                if (parentRect) {
                    setPillStyle({
                        left: `${el.offsetLeft + 10}px`,
                        width: `${el.offsetWidth - 20}px`,
                        top: '4px',
                        height: 'calc(100% - 8px)'
                    });
                }
            }
        };

        // Use requestAnimationFrame to wait until layout stabilizes
        requestAnimationFrame(updatePill);
        window.addEventListener("resize", updatePill);
        return () => window.removeEventListener("resize", updatePill);
    }, [activeTabKey, location.pathname]);
    return (
        <div className="mb-2">
            <div className="relative inline-flex bg-gray-100 p-1 rounded-[15px]">
                {/* Animated pill */}
                <span
                    className="absolute bg-primary-light z-0 shadow rounded-[15px] transition-all duration-300 ease-in-out"
                    style={pillStyle}
                />

                {/* Tab items */}
                {Object.entries(TABS).map(([key, {label, icon: Icon, path}]) => (
                    <NavLink
                        key={key}
                        to={path}
                        ref={(el) => (tabRefs.current[key] = el)}
                        className={({isActive}) =>
                            `relative z-10 flex items-center text-white gap-[5px] px-6 py-2 text-sm transition-all duration-200 rounded-md ${
                                isActive
                                    ? 'font-semibold'
                                    : 'text-gray-600 hover:text-primary-dark  hover:text-shadow-md transition'
                            }`
                        }
                    >
                        <Icon className={
                            `${activeTabKey === key ? 'text-white' : 'hover:text-primary-dark hover:text-shadow-md transition'
                            }`
                        }
                              size={18} weight="bold"/>
                        {label}
                    </NavLink>
                ))}
            </div>
        </div>
    );
};

export default TabsNav;
