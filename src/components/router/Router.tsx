import React from 'react';
import { Route, Routes } from 'react-router-dom';
import {MainLayout} from "./MainLayout";
import {Settings, Logs , About} from "../../screens";


export const Router: React.FC = () => {
    return (
        <Routes>
            <Route path="/" element={<MainLayout />}>
                <Route index element={<Settings />} />  
                <Route path="settings" element={<Settings />} />
                <Route path="logs" element={<Logs />} />
                <Route path="about-us" element={<About />} />
            </Route>
        </Routes>
    );
};
