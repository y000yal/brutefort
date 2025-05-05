import React from "react";
import { ShieldCheck, ListPlus, Prohibit } from '@phosphor-icons/react';

export const TABS: Record<string, { label: string; icon: React.ElementType , path: string }> = {
    general: {
        label: 'General',
        icon: ShieldCheck,
        path: 'general',
    },
    whitelist: {
        label: 'Whitelist',
        icon: ListPlus,
        path:'whitelist',
    },
    blacklist: {
        label: 'Blacklist',
        icon: Prohibit,
        path: 'blacklist'
    },
};
