import React from 'react';
import {createRoot} from 'react-dom/client';
import {App} from "./App";

const root = document.getElementById('brutefort-admin-app');
if (root) {
    createRoot(root).render(<App/>);
}
