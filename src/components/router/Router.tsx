import React from 'react';
import { Route, Routes } from 'react-router-dom';
import {MainLayout} from "./MainLayout";
import {General, WhiteList , BlackList} from "../../screens";


export const Router: React.FC = () => {
    return (
        <Routes>
            <Route path="/" element={<MainLayout />}>
                <Route index element={<General />} />  {/* Default route */}
                <Route path="general" element={<General />} />  {/* Explicit path */}
                <Route path="whitelist" element={<WhiteList />} />  {/* Explicit path */}
                <Route path="blacklist" element={<BlackList />} />  {/* Explicit path */}
            </Route>
        </Routes>
    );
};
