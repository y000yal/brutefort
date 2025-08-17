import React from "react";
import {Wrench, ReadCvLogo, UsersThree} from '@phosphor-icons/react';

const TABS: Record<string, { label: string; icon: React.ElementType, path: string }> = {
    settings: {
        label: 'Settings',
        icon: Wrench,
        path: 'settings',
    },
    logs: {
        label: 'Logs',
        icon: ReadCvLogo,
        path: 'logs',
    },
    blacklist: {
        label: 'About Us',
        icon: UsersThree,
        path: 'about-us'
    },
};

export default TABS;