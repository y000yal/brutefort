import React from "react";
import {Input} from "../forms";

export const IpLogs = ({data}) => {
    return (
        <div id="ip-wrapper" className="w-[20%] p-4 gap-2 flex flex-col"
             style={{borderRight: '2px solid #ded5d552'}}>

            <div id="ip-list">
                <ul>
                    <li className="ip-item text-sm flex flex-col align-left">
                        <span>192.168.5.1</span>
                        <span className="ip-status locked">locked</span>
                    </li>
                </ul>

            </div>
        </div>

    )
}