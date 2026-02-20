import type { ColumnDef } from "@tanstack/react-table";
import { Loader2, Pencil, Plus, Trash2, Users, X } from "lucide-react";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";

import type { User, UserListParams } from "@/api/rest/client";
import { usersApi } from "@/api/rest/client";
import { DataTable } from "@/components/data-table/data-table";
import { DataTableColumnHeader } from "@/components/data-table/data-table-column-header";
import { DataTableViewOptions } from "@/components/data-table/data-table-view-options";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from "@/components/ui/empty";
import { useDataTable } from "@/hooks/use-data-table";
import { parseAsInteger, parseAsString, useQueryStates } from "nuqs";
import UserFormModal from "./components/UserFormModal";

const UserManagementPage = () => {
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [totalPages, setTotalPages] = useState<number>(1);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState<boolean>(false);
  const [userToDelete, setUserToDelete] = useState<User | null>(null);
  const [isDeleting, setIsDeleting] = useState<boolean>(false);
  const [userFormModalOpen, setUserFormModalOpen] = useState<boolean>(false);
  const [userFormMode, setUserFormMode] = useState<"create" | "edit">("create");
  const [userToEdit, setUserToEdit] = useState<User | undefined>(undefined);

  const [{ page, perPage, name, email }, setQueryStates] = useQueryStates({
    page: parseAsInteger.withDefault(1),
    perPage: parseAsInteger.withDefault(10),
    name: parseAsString.withDefault(""),
    email: parseAsString.withDefault(""),
  });

  const [localName, setLocalName] = useState<string>(name);
  const [localEmail, setLocalEmail] = useState<string>(email);
  const debounceTimerRef = useRef<NodeJS.Timeout | null>(null);

  const columns = useMemo<ColumnDef<User>[]>(
    () => [
      {
        id: "no",
        header: "No.",
        cell: ({ row, table }) => {
          const pageIndex = table.getState().pagination.pageIndex;
          const pageSize = table.getState().pagination.pageSize;
          const rowIndex = row.index;
          const rowNumber = pageIndex * pageSize + rowIndex + 1;
          return <div className="text-sm">{rowNumber}</div>;
        },
        meta: { label: "No." },
        enableColumnFilter: false,
      },
      {
        id: "name",
        accessorKey: "name",
        header: ({ column }) => (
          <DataTableColumnHeader column={column} label="Name" />
        ),
        cell: ({ cell }) => <div>{cell.getValue<string>()}</div>,
        meta: { label: "Name", variant: "text" },
        enableColumnFilter: true,
      },
      {
        id: "email",
        accessorKey: "email",
        header: ({ column }) => (
          <DataTableColumnHeader column={column} label="Email" />
        ),
        cell: ({ cell }) => <div>{cell.getValue<string>()}</div>,
        meta: { label: "Email", variant: "text" },
        enableColumnFilter: true,
      },
      {
        id: "created_at",
        accessorKey: "created_at",
        header: ({ column }) => (
          <DataTableColumnHeader column={column} label="Created At" />
        ),
        cell: ({ cell }) => {
          const date = new Date(cell.getValue<string>());
          return <div>{date.toLocaleString()}</div>;
        },
        meta: { label: "Created At" },
        enableColumnFilter: false,
      },
      {
        id: "actions",
        header: "Actions",
        cell: ({ row }) => {
          const user = row.original;
          return (
            <div className="flex items-center gap-2">
              <Button
                variant="ghost"
                size="icon"
                onClick={() => {
                  setUserToEdit(user);
                  setUserFormMode("edit");
                  setUserFormModalOpen(true);
                }}
                className="h-8 w-8 text-muted-foreground hover:text-primary"
                title="Edit user"
              >
                <Pencil className="h-4 w-4" />
              </Button>
              <Button
                variant="ghost"
                size="icon"
                onClick={() => {
                  setUserToDelete(user);
                  setDeleteDialogOpen(true);
                }}
                className="h-8 w-8 text-muted-foreground hover:text-destructive"
              >
                <Trash2 className="h-4 w-4" />
              </Button>
            </div>
          );
        },
        meta: { label: "Actions" },
        enableColumnFilter: false,
      },
    ],
    [],
  );

  const { table } = useDataTable({
    data: users,
    columns,
    pageCount: totalPages,
    initialState: {
      sorting: [{ id: "created_at", desc: true }],
    },
    getRowId: (row) => row.id.toString(),
  });

  // Sync local state with URL params when they change externally
  useEffect(() => {
    setLocalName(name);
    setLocalEmail(email);
  }, [name, email]);

  // Debounced filter update
  useEffect(() => {
    if (debounceTimerRef.current) {
      clearTimeout(debounceTimerRef.current);
    }

    debounceTimerRef.current = setTimeout(() => {
      setQueryStates({
        name: localName,
        email: localEmail,
        page: 1, // Reset to first page on filter change
      });
    }, 500);

    return () => {
      if (debounceTimerRef.current) {
        clearTimeout(debounceTimerRef.current);
      }
    };
  }, [localName, localEmail, setQueryStates]);

  const fetchUsers = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const params: UserListParams = {
        page,
        per_page: perPage,
      };

      if (name) {
        params.name = name;
      }
      if (email) {
        params.email = email;
      }

      const response = await usersApi.list(params);
      setUsers(response.data);
      setTotalPages(response.meta.last_page);
    } catch (err) {
      setError("Failed to fetch users. Please try again.");
      console.error("Error fetching users:", err);
    } finally {
      setLoading(false);
    }
  }, [page, perPage, name, email]);

  const handleDeleteUser = useCallback(async () => {
    if (!userToDelete) return;

    setIsDeleting(true);
    try {
      await usersApi.delete(userToDelete.id);
      await fetchUsers();
      setDeleteDialogOpen(false);
      setUserToDelete(null);
    } catch (err) {
      setError("Failed to delete user. Please try again.");
      console.error("Error deleting user:", err);
    } finally {
      setIsDeleting(false);
    }
  }, [userToDelete, fetchUsers]);

  useEffect(() => {
    void fetchUsers();
  }, [fetchUsers]);

  if (error) {
    return (
      <>
        <div className="flex items-center justify-between mb-4">
          <h1 className="text-2xl font-bold">User Management</h1>
        </div>
        <div className="rounded-md border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-400">
          {error}
        </div>
      </>
    );
  }

  return (
    <>
      <div className="flex items-center justify-between mb-4">
        <h1 className="text-2xl font-bold">User Management</h1>
        <Button
          onClick={() => {
            setUserToEdit(undefined);
            setUserFormMode("create");
            setUserFormModalOpen(true);
          }}
        >
          <Plus className="mr-2 h-4 w-4" />
          Create User
        </Button>
      </div>
      <div className="rounded-md bg-card text-card-foreground">
        <div className="flex w-full items-start justify-between gap-2 pb-4">
          <div className="flex flex-1 flex-wrap items-center gap-2">
            <Input
              placeholder="Filter by name..."
              value={localName}
              onChange={(e) => setLocalName(e.target.value)}
              className="h-8 w-40 lg:w-56"
            />
            <Input
              placeholder="Filter by email..."
              value={localEmail}
              onChange={(e) => setLocalEmail(e.target.value)}
              className="h-8 w-40 lg:w-56"
            />
            {(localName || localEmail) && (
              <Button
                variant="outline"
                size="sm"
                className="border-dashed"
                onClick={() => {
                  setLocalName("");
                  setLocalEmail("");
                }}
              >
                <X />
                Reset
              </Button>
            )}
          </div>
          <div className="flex items-center gap-2">
            <DataTableViewOptions table={table} align="end" />
          </div>
        </div>
        {loading ? (
          <div className="p-8 flex items-center justify-center">
            <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
          </div>
        ) : users.length === 0 ? (
          <div className="p-8">
            <Empty>
              <EmptyHeader>
                <EmptyMedia variant="icon">
                  <Users />
                </EmptyMedia>
                <EmptyTitle>No users found</EmptyTitle>
                <EmptyDescription>
                  There are no users yet. Create your first user to get started.
                </EmptyDescription>
              </EmptyHeader>
              <EmptyContent>
                <Button
                  onClick={() => {
                    setUserToEdit(undefined);
                    setUserFormMode("create");
                    setUserFormModalOpen(true);
                  }}
                >
                  <Plus className="mr-2 h-4 w-4" />
                  Create User
                </Button>
              </EmptyContent>
            </Empty>
          </div>
        ) : (
          <DataTable table={table} />
        )}
      </div>

      <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Delete User</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure? This action cannot be undone.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel disabled={isDeleting}>Cancel</AlertDialogCancel>
            <AlertDialogAction onClick={handleDeleteUser} disabled={isDeleting}>
              {isDeleting ? (
                <>
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                  Deleting...
                </>
              ) : (
                "Delete"
              )}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <UserFormModal
        open={userFormModalOpen}
        onOpenChange={setUserFormModalOpen}
        mode={userFormMode}
        user={userToEdit}
        onSuccess={fetchUsers}
      />
    </>
  );
};

export default UserManagementPage;
