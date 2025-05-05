import React, { useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { Shield  } from '@phosphor-icons/react';

export const IconInjector: React.FC = () => {
    useEffect(() => {
        const selector = '#adminmenu .toplevel_page_brutefort .wp-menu-image';
        const menuIconWrapper = document.querySelector(selector);

        // Skip if not found or icon already injected
        if (!menuIconWrapper || menuIconWrapper.querySelector('.brutefort-react-icon')) {
            return;
        }

        const container = document.createElement('div');
        container.className = 'brutefort-react-icon';
        container.style.display = 'flex';
        container.style.alignItems = 'center';
        container.style.justifyContent = 'center';
        container.style.height = '20px'; // match icon size

        menuIconWrapper.appendChild(container);

        const icon = (
            <Shield
                size={20}
                color="currentColor"
                weight="fill"
                style={{ position: "absolute", top: "6px" , left: "8px" }}
            />
        );

        const root = createRoot(container);
        root.render(icon);

        // Optionally store the root in case you want to unmount later
        (window as any).brutefortIconRoot = root;

    }, []);

    return null;
};
