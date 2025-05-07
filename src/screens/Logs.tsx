import React from "react";
import {SETTINGS} from "../constants/settings";

const Logs = () => {
    return (
        <div
            className=" p-4  rounded-lg w-full items-center justify-center transition-colors flex  duration-300 gap-4 ">
            <div className="min-w-lg">
                <div className="header mb-5">
                    <span className="text-2xl font-bold ">Logs</span>
                    <p style={{marginTop: '5px'}}>View your logs here.</p>

                </div>
                <hr/>
                <div className="settings-body flex flex-col mt-5">


                </div>
            </div>
        </div>
    );
};

export default Logs;

