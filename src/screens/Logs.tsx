import React, {useEffect} from "react";
import {useQuery} from '@tanstack/react-query';
import api from "../axios/api";
import {CONSTANTS} from "../constants";

const Logs = () => {
    const fetchRoute = CONSTANTS.LOG_ROUTES.getAll;

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

