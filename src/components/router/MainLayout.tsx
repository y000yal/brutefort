import React, { useEffect, useRef, useState } from "react";
import { Outlet } from "react-router-dom";
import TabsNav from "../TabsNav";
import { ShieldSlash } from "@phosphor-icons/react";
import ThemeToggle from "../ThemeToggle";
import { __ } from "@wordpress/i18n";
import { Copyright } from "@phosphor-icons/react";
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
      <div className="flex gap-[20px]">
        <div className="w-[20%] h-[50%] bg-white dark:bg-gray-800 rounded-md  p-4">
          <TabsNav />
        </div>

        <div className="w-[80%] min-h-[80vh] flex flex-col justify-between bg-white rounded-md dark:bg-gray-800 dark:text-white">
          <div className="p-[30px]  overflow-y-auto overflow-hidden">
            <Outlet />
          </div>
        
        </div>
      </div>
    </div>
  );
};
