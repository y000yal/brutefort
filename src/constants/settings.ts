import React from "react";
import {Gauge, MapPin} from "@phosphor-icons/react";
import RateLimit from "../screens/Settings/RateLimit";
import IpSettings from "../screens/Settings/IpSettings";
import {SettingComponentType} from "../types";

const SETTINGS: Record<string, { id: string, label: string; icon: React.ElementType; component: SettingComponentType, description: string, globalSave : boolean; Routes: Record<string, any> }> = {
    rateLimitSettings: {
        id: "rate-limit-settings",
        label: "Rate Limit Settings",
        icon: Gauge,
        component: RateLimit,
        description: "All settings related with rate limiting, intervals , limit extensions can be found here.",
        globalSave: true,
        Routes: {
            Save: {
                value: '/',
                type: 'POST'
            },
            Index: {
                value: '/',
                type: 'GET'
            }
        }
    },
    ipSettings: {
        id: "ip-settings",
        label: "IP Settings",
        icon: MapPin,
        component: IpSettings,
        description: "Ip settings, from whitelisting to direct blacklisting can be found in this section.",
        globalSave: false,
        Routes: {
            Save: {
                value: '/',
                type: 'POST'
            },
            Index: {
                value: '/',
                type: 'GET'
            },
            Delete: {
                value: '/delete',
                type: 'DELETE'
            }
        }
    },
};

export default SETTINGS;
