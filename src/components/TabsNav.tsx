import React, {useEffect, useRef, useState} from "react";
import {NavLink, useLocation} from "react-router-dom";
import {TABS} from "../constants";

const TabsNav: React.FC = () => {
    const tabRefs = useRef<Record<string, HTMLAnchorElement | null>>({});
    const [pillStyle, setPillStyle] = useState<React.CSSProperties>({});
    const location = useLocation();
    // Detect active tab key based on path
    const activeTabKey = Object.entries(TABS).find(
        ([, {path}]) => '/' + path === (location.pathname.length > 1 ? location.pathname : '/settings')
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
                        top: `${el.offsetTop}px`,
                        height: `${el.offsetHeight}px`,
                        left: '4px',
                        width: 'calc(100% - 8px)'
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

        <div className="relative flex flex-col dark:text-white p-1 rounded-[15px]">
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
                    ref={(el) => { tabRefs.current[key] = el; }}
                    className={({isActive}) =>
                        `relative z-10 flex items-center text-white dark:text-white gap-[15px] px-6 py-2 text-sm transition-all duration-200 rounded-md ${
                            isActive
                                ? 'font-semibold'
                                : 'text-gray-600 hover:text-primary-dark dark:hover:text-gray-100 transition dark:text-gray-300'
                        }`
                    }
                >
                    <Icon className={
                        `dark:text-white ${activeTabKey === key ? 'text-white' : 'hover:text-primary-dark dark:hover:text-gray-100 transition'
                        }`
                    }
                          size={18} weight="bold"/>
                    {label}
                </NavLink>
            ))}
        </div>

    );
};

export default TabsNav;
