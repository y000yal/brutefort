import React, {useEffect, useRef, useState} from 'react';
import { Outlet} from 'react-router-dom';
import TabsNav from "../TabsNav";
import {ShieldSlash} from "@phosphor-icons/react";
import ThemeToggle from "../ThemeToggle";

export const MainLayout: React.FC = () => {
    return (
        <div className="pr-6 pl-0 max-w-8xl force-tailwind dark:text-gray-700">
            <div className="flex justify-between mb-4">
                <div className="flex items-center gap-2 dark:text-white">
                    <ShieldSlash size={32} />
                    <span className="text-3xl font-bold ">BruteFort</span>
                </div>
                <ThemeToggle />
            </div>

            <TabsNav />

            <div className="bg-white p-4 rounded-md dark:bg-gray-800 dark:text-white">
                <Outlet />
            </div>
        </div>
    );
};


