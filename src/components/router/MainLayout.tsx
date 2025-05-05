import React, {useEffect, useRef, useState} from 'react';
import { Outlet} from 'react-router-dom';
import TabsNav from "../TabsNav";
import {ShieldSlash} from "@phosphor-icons/react";
import ThemeToggle from "../ThemeToggle";

export const MainLayout: React.FC = () => {
    return (
        <div className="pr-6 pl-0 max-w-8xl force-tailwind">
            <div className="flex justify-between mb-4">
                <div className="flex items-center gap-2">
                    <ShieldSlash size={32} />
                    <h1 className="text-3xl font-bold mb-6 dark:text-gray-200">BruteFort</h1>
                </div>
                <ThemeToggle />
            </div>

            <TabsNav />

            <div className="bg-white p-6 rounded-md shadow dark:bg-gray-800 dark:text-white">
                <Outlet />
            </div>
        </div>
    );
};


