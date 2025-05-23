import React from "react";
import {Wrench, ReadCvLogo, UsersThree} from '@phosphor-icons/react';

const TABS: Record<string, { label: string; icon: React.ElementType, path: string }> = {
    general: {
        label: 'General',
        icon: Wrench,
        path: 'general',
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