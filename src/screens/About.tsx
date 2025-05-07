import React from "react";

const About = () => {
    return (
        <div
            className=" p-4  rounded-lg w-full items-center justify-center transition-colors flex  duration-300 gap-4 ">
            <div className="min-w-lg max-w-sm">
                <div className="header mb-5">
                    <span className="text-2xl font-bold">About BruteFort</span>
                    <p style={{marginTop: '5px'}}>Our mission and what we aim for.</p>
                </div>
                <hr/>
                <div className="settings-body flex flex-col mt-5">
                    <i className="mb-1">"Your First Line of Defense Against Brute Force"</i>
                    <span>
                        At <b>BruteFort</b>, our mission is to empower WordPress users around the world with rock-solid, intelligent security solutions—built with care in Nepal.
We’re committed to defending digital freedom by making cutting-edge protection tools accessible, lightweight, and easy to use.
Our goal is to eliminate brute-force attacks and malicious intrusions so that creators, businesses, and developers can build fearlessly.
                    </span>
                </div>
            </div>
        </div>
    );
};

export default About;

