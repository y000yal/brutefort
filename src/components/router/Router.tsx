import React from 'react';
import { Route, Routes } from 'react-router-dom';
import {MainLayout} from "./MainLayout";
import {General, Logs , About} from "../../screens";


export const Router: React.FC = () => {
    return (
        <Routes>
            <Route path="/" element={<MainLayout />}>
                <Route index="general" element={<General />} />  {/* Default route */}
                <Route path="general" element={<General />} />
                <Route path="logs" element={<Logs />} />
                <Route path="about-us" element={<About />} />
            </Route>
        </Routes>
    );
};
