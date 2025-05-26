import React, {useEffect, useState} from "react";
import {useQuery} from '@tanstack/react-query';
import api from "../axios/api";
import {CONSTANTS} from "../constants";
import {Input} from "../components/forms";
import {IpLogs} from "../components/logs/IpLogs";
import {IpLogDetails} from "../components/logs/IpLogDetails";
import {MagnifyingGlass} from "@phosphor-icons/react";

const Logs = () => {
    const fetchRoute = CONSTANTS.LOG_ROUTES.getAll;
    const [searchInput, setSearchInput] = useState('');
    const [hasSearchInput, setHasSearchInput] = useState(false);

    const {data, isLoading} = useQuery({
        initialData: undefined,
        queryKey: ['logs'],
        queryFn: async () => {
            const res = await api.get(fetchRoute);
            return res.data.data;
        },
        staleTime: Infinity,
        enabled: !!fetchRoute
    });
    useEffect(() => {
        if (data) {
            console.log(data)
        }
    }, [data]);
    const handleSearchBarChange = (e) => {
        const value = e.target.value;
        setSearchInput(value);
        setHasSearchInput(value.length > 0);
    }
    return (
        <div
            className=" p-4 rounded-lg w-full  transition-colors  duration-300 gap-4 ">
            <div className="header mb-5">
                <span className="text-2xl font-bold ">Logs</span>
            </div>
            <div className="settings-body flex gap-[20px] mt-5">
                <div className="search-box">
                    <label htmlFor="search-bar">
                        <MagnifyingGlass size={24} style={{
                            position: "absolute",
                            zIndex: 10,
                            top: "11px",
                            left: "12px",
                            color: "gray"
                        }}/>
                    </label>

                    <Input
                        id="search-bar"
                        className="rounded-lg"
                        placeholder="192.168.11.12"
                        value={searchInput}
                        onChange={handleSearchBarChange}
                    />
                    {hasSearchInput && (
                        <div className="right-search-bar">
                            <span className="search-bar-btn search">Search</span>
                            <span className="search-bar-btn clear" onClick={()=> {
                                setSearchInput("")
                                setHasSearchInput(false);
                            }}>Clear</span>
                        </div>
                    )}


                </div>
            </div>
        </div>
    );
};

export default Logs;

