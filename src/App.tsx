import React, {useEffect, useRef, useState} from "react";

import './styles/admin.css';
import {HashRouter, Routes, Route, Navigate} from 'react-router-dom';
import {Router} from "./components/router/Router";
import {IconInjector} from "./components/IconInjector";

export const App: React.FC = () => {
    return (
        <HashRouter>
            <IconInjector />
            <Router/>
        </HashRouter>

    );
};