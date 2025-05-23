import React from 'react';
import DataTable from "../components/table/DataTable";
import { ColumnDef } from '@tanstack/react-table';
import { IconButton, Menu, MenuItem } from '@mui/material';
import MoreVertIcon from '@mui/icons-material/MoreVert';

interface Payment {
    status: string;
    email: string;
    amount: string;
}

const payments: Payment[] = [
    { status: 'Success', email: 'ken99@example.com', amount: '$316.00' },
    { status: 'Failed', email: 'carmella@example.com', amount: '$0.00' },
];

const Logs = () => {
    const [anchorEl, setAnchorEl] = React.useState<null | HTMLElement>(null);
    const [selected, setSelected] = React.useState<Payment | null>(null);

    const columns: ColumnDef<Payment>[] = [
        { accessorKey: 'status', header: 'Status' },
        { accessorKey: 'email', header: 'Email' },
        { accessorKey: 'amount', header: 'Amount' },
    ];

    const rowActions = (row: Payment) => (
        <>
            <IconButton
                onClick={(e) => {
                    setAnchorEl(e.currentTarget);
                    setSelected(row);
                }}
            >
                <MoreVertIcon />
            </IconButton>
        </>
    );

    return (
        <>
            <DataTable columns={columns} data={payments} actions={rowActions} />

            <Menu
                anchorEl={anchorEl}
                open={Boolean(anchorEl)}
                onClose={() => setAnchorEl(null)}
            >
                <MenuItem onClick={() => navigator.clipboard.writeText(selected?.email ?? '')}>Copy Payment ID</MenuItem>
                <MenuItem onClick={() => alert(`Customer: ${selected?.email}`)}>View Customer</MenuItem>
                <MenuItem onClick={() => alert(`Details for: ${selected?.email}`)}>View Payment Details</MenuItem>
            </Menu>
        </>
    );
};

export default Logs;
