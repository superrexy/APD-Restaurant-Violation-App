import { zodResolver } from "@hookform/resolvers/zod";
import {
  ChevronLeft,
  ChevronRight,
  Loader2,
  MoreHorizontal,
  ShieldAlert,
} from "lucide-react";
import { useCallback, useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { toast } from "sonner";
import { z } from "zod";

import type { Violation } from "@/api/rest/client";
import { violationDetailsApi, violationsApi } from "@/api/rest/client";
import useRestApi from "@/hooks/use-rest-api";
import { parseAsInteger, useQueryStates } from "nuqs";

import {
  AlertDialog,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { AspectRatio } from "@/components/ui/aspect-ratio";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Empty,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from "@/components/ui/empty";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Status, StatusIndicator, StatusLabel } from "@/components/ui/status";
import { Textarea } from "@/components/ui/textarea";
import { formatDate } from "@/lib/format";

const statusUpdateSchema = z.object({
  status: z.enum(["pending", "reviewed", "resolved"]),
  notes: z.string().optional(),
});

type StatusUpdateValues = z.infer<typeof statusUpdateSchema>;

interface StatusUpdateDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  violationId: number;
  currentStatus: "pending" | "reviewed" | "resolved";
  currentNotes: string | null;
  onSuccess?: () => void;
}

const StatusUpdateDialog = ({
  open,
  onOpenChange,
  violationId,
  currentStatus,
  currentNotes,
  onSuccess,
}: StatusUpdateDialogProps) => {
  const { callAPI } = useRestApi();

  const form = useForm<StatusUpdateValues>({
    resolver: zodResolver(statusUpdateSchema),
    defaultValues: {
      status: currentStatus,
      notes: currentNotes || "",
    },
  });

  useEffect(() => {
    if (open) {
      form.reset({
        status: currentStatus,
        notes: currentNotes || "",
      });
    }
  }, [open, currentStatus, currentNotes, form]);

  const onSubmit = async (values: StatusUpdateValues) => {
    try {
      await callAPI(() => violationsApi.updateStatus(violationId, values));
      toast.success("Status updated successfully");
      form.reset();
      onOpenChange(false);
      onSuccess?.();
    } catch (error) {
      console.error("Error updating status:", error);
      toast.error("Failed to update status. Please try again.");
    }
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Update Violation Status</DialogTitle>
          <DialogDescription>
            Update the status of this violation record.
          </DialogDescription>
        </DialogHeader>
        <Form {...form}>
          <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
            <FormField
              control={form.control}
              name="status"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Status</FormLabel>
                  <Select
                    onValueChange={field.onChange}
                    defaultValue={field.value}
                  >
                    <FormControl>
                      <SelectTrigger className="w-full">
                        <SelectValue placeholder="Select status" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectItem value="pending">Pending</SelectItem>
                      <SelectItem value="reviewed">Reviewed</SelectItem>
                      <SelectItem value="resolved">Resolved</SelectItem>
                    </SelectContent>
                  </Select>
                  <FormMessage />
                </FormItem>
              )}
            />
            <FormField
              control={form.control}
              name="notes"
              render={({ field }) => (
                <FormItem>
                  <FormLabel>Notes</FormLabel>
                  <FormControl>
                    <Textarea
                      placeholder="Optional notes..."
                      className="min-h-[100px]"
                      {...field}
                    />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              )}
            />
            <DialogFooter>
              <Button
                type="button"
                variant="outline"
                onClick={() => onOpenChange(false)}
                disabled={form.formState.isSubmitting}
              >
                Cancel
              </Button>
              <Button type="submit" disabled={form.formState.isSubmitting}>
                {form.formState.isSubmitting ? (
                  <>
                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                    Updating...
                  </>
                ) : (
                  "Save"
                )}
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
};

interface ImageZoomDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  image: Violation | null;
}

const ImageZoomDialog = ({
  open,
  onOpenChange,
  image,
}: ImageZoomDialogProps) => {
  const { callAPI } = useRestApi();
  const [violationDetails, setViolationDetails] = useState<Violation | null>(
    null,
  );
  const [isLoadingDetails, setIsLoadingDetails] = useState(false);

  useEffect(() => {
    if (open && image) {
      setIsLoadingDetails(true);
      callAPI(() => violationsApi.get(image.id))
        .then((response) => {
          if (response) {
            setViolationDetails(response.data);
          }
        })
        .catch((error) => {
          console.error("Error fetching violation details:", error);
          toast.error("Failed to fetch violation details");
        })
        .finally(() => {
          setIsLoadingDetails(false);
        });
    }
  }, [open, image, callAPI]);

  const handleUpdateDetailStatus = async (
    detailId: number,
    newStatus: "confirmed" | "dismissed",
  ) => {
    try {
      await callAPI(() =>
        violationDetailsApi.updateStatus(detailId, { status: newStatus }),
      );
      toast.success(`Violation detail ${newStatus}`);

      if (violationDetails) {
        setViolationDetails({
          ...violationDetails,
          violation_details: violationDetails.violation_details?.map(
            (detail) =>
              detail.id === detailId
                ? { ...detail, status: newStatus }
                : detail,
          ),
        });
      }
    } catch (error) {
      console.error("Error updating violation detail status:", error);
      toast.error("Failed to update violation detail status");
    }
  };

  if (!image) return null;

  const currentViolation = violationDetails || image;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-6xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Violation Details</DialogTitle>
        </DialogHeader>

        <div className="space-y-6">
          {/* Image */}
          <div className="relative">
            <img
              src={`${import.meta.env.VITE_BASE_API_URL}/storage/${currentViolation.image_path}`}
              alt={`Violation #${currentViolation.id}`}
              className="w-full h-auto rounded-lg"
            />
          </div>

          {/* Violation Information */}
          <div className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <p className="text-sm text-muted-foreground">Camera</p>
                <p className="font-medium">
                  {currentViolation.camera?.name ||
                    `Camera #${currentViolation.camera_id}`}
                </p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Detected</p>
                <p className="font-medium">
                  {formatDate(currentViolation.created_at, {
                    month: "short",
                    day: "numeric",
                    year: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                  })}
                </p>
              </div>
            </div>

            {currentViolation.notes && (
              <div>
                <p className="text-sm text-muted-foreground mb-1">Notes</p>
                <p className="text-sm">{currentViolation.notes}</p>
              </div>
            )}
          </div>

          {/* Violation Details */}
          <div className="space-y-3">
            <h3 className="text-lg font-semibold">Violation Details</h3>

            {isLoadingDetails ? (
              <div className="flex items-center justify-center py-8">
                <Loader2 className="h-8 w-8 animate-spin" />
              </div>
            ) : currentViolation.violation_details &&
              currentViolation.violation_details.length > 0 ? (
              <div className="space-y-3">
                {currentViolation.violation_details.map((detail) => (
                  <div
                    key={detail.id}
                    className="border rounded-lg p-4 space-y-3"
                  >
                    <div className="flex items-start justify-between">
                      <div className="flex-1">
                        <p className="font-medium text-sm">
                          {detail.violation_type?.name || "Unknown Type"}
                        </p>
                        {detail.violation_type?.code && (
                          <p className="text-xs text-muted-foreground">
                            Code: {detail.violation_type.code}
                          </p>
                        )}
                      </div>
                      <Status
                        variant={
                          detail.status === "confirmed"
                            ? "success"
                            : detail.status === "dismissed"
                              ? "default"
                              : "warning"
                        }
                        className="capitalize"
                      >
                        <StatusIndicator />
                        <StatusLabel>{detail.status}</StatusLabel>
                      </Status>
                    </div>

                    {detail.confidence_score !== null && (
                      <div className="flex items-center justify-between text-sm">
                        <span className="text-muted-foreground">
                          Confidence Score:
                        </span>
                        <span className="font-medium">
                          {Math.round(detail.confidence_score * 100)}%
                        </span>
                      </div>
                    )}

                    {detail.additional_info && (
                      <div>
                        <p className="text-xs text-muted-foreground mb-1">
                          Additional Info
                        </p>
                        <p className="text-sm">{detail.additional_info}</p>
                      </div>
                    )}

                    {detail.status !== "confirmed" &&
                      detail.status !== "dismissed" && (
                        <div className="flex gap-2 pt-2">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() =>
                              handleUpdateDetailStatus(detail.id, "dismissed")
                            }
                          >
                            Dismiss
                          </Button>
                          <Button
                            size="sm"
                            onClick={() =>
                              handleUpdateDetailStatus(detail.id, "confirmed")
                            }
                          >
                            Confirm
                          </Button>
                        </div>
                      )}
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-center text-muted-foreground py-4">
                No violation details found
              </p>
            )}
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
};

interface DeleteConfirmDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  violation: Violation | null;
  onSuccess?: () => void;
}

const DeleteConfirmDialog = ({
  open,
  onOpenChange,
  violation,
  onSuccess,
}: DeleteConfirmDialogProps) => {
  const { callAPI } = useRestApi();
  const [isDeleting, setIsDeleting] = useState(false);

  const handleDelete = async () => {
    if (!violation) return;
    try {
      setIsDeleting(true);
      await callAPI(async () => {
        await violationsApi.delete(violation.id);
        return {
          statusCode: 204,
          message: "Deleted",
          data: null,
          meta: {},
        } as const;
      });
      toast.success("Violation deleted successfully");
      onOpenChange(false);
      onSuccess?.();
    } catch (error) {
      console.error("Error deleting violation:", error);
      toast.error("Failed to delete violation. Please try again.");
    } finally {
      setIsDeleting(false);
    }
  };

  return (
    <AlertDialog open={open} onOpenChange={onOpenChange}>
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Violation</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete this violation record? This action
            cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel disabled={isDeleting}>Cancel</AlertDialogCancel>
          <Button
            variant="destructive"
            onClick={handleDelete}
            disabled={isDeleting}
          >
            {isDeleting ? (
              <>
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                Deleting...
              </>
            ) : (
              "Delete"
            )}
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
};

const ViolationHistoryPage = () => {
  const [violations, setViolations] = useState<Violation[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | undefined>(undefined);
  const [totalPages, setTotalPages] = useState<number>(1);
  const [statusDialogOpen, setStatusDialogOpen] = useState<boolean>(false);
  const [selectedViolation, setSelectedViolation] = useState<Violation | null>(
    null,
  );
  const [imageDialogOpen, setImageDialogOpen] = useState<boolean>(false);
  const [selectedViolationForImage, setSelectedViolationForImage] =
    useState<Violation | null>(null);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState<boolean>(false);
  const [selectedViolationForDelete, setSelectedViolationForDelete] =
    useState<Violation | null>(null);
  const [statusFilter, setStatusFilter] = useState<string | undefined>(
    undefined,
  );

  const [{ page, per_page }, setPage] = useQueryStates({
    page: parseAsInteger.withDefault(1),
    per_page: parseAsInteger.withDefault(12),
  });

  const getStatusVariant = (status: string) => {
    switch (status) {
      case "pending":
        return "warning";
      case "reviewed":
        return "info";
      case "resolved":
        return "success";
      default:
        return "default";
    }
  };

  const getViolationTypes = (violation: Violation) => {
    if (
      !violation.violation_details ||
      violation.violation_details.length === 0
    ) {
      return "Unknown";
    }
    return violation.violation_details
      .map((detail) => detail.violation_type?.name || "Unknown")
      .join(", ");
  };

  const fetchViolations = useCallback(async () => {
    setIsLoading(true);
    setError(undefined);
    try {
      const response = await violationsApi.list({
        page,
        per_page,
        status: statusFilter || undefined,
      });
      setViolations(response.data);
      setTotalPages(response.meta.last_page);
    } catch (err) {
      setError("Failed to fetch violations. Please try again.");
      console.error("Error fetching violations:", err);
    } finally {
      setIsLoading(false);
    }
  }, [page, per_page, statusFilter]);

  useEffect(() => {
    void fetchViolations();
  }, [fetchViolations]);

  const handleStatusUpdateSuccess = () => {
    void fetchViolations();
  };

  const handleDelete = (violation: Violation) => {
    setSelectedViolationForDelete(violation);
    setDeleteDialogOpen(true);
  };

  if (error) {
    return (
      <>
        <div className="flex items-center justify-between mb-4">
          <h1 className="text-2xl font-bold">Violation History</h1>
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
        <h1 className="text-2xl font-bold">Violation History</h1>
        <Select
          value={statusFilter ?? "all"}
          onValueChange={(value: string) => {
            setStatusFilter(value === "all" ? undefined : value);
            setPage({ page: 1, per_page });
          }}
        >
          <SelectTrigger className="w-40">
            <SelectValue placeholder="All Status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Status</SelectItem>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="reviewed">Reviewed</SelectItem>
            <SelectItem value="resolved">Resolved</SelectItem>
          </SelectContent>
        </Select>
      </div>
      {isLoading ? (
        <div className="p-8 flex items-center justify-center">
          <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
        </div>
      ) : violations.length === 0 ? (
        <div className="p-8">
          <Empty>
            <EmptyHeader>
              <EmptyMedia variant="icon">
                <ShieldAlert />
              </EmptyMedia>
              <EmptyTitle>No violations found</EmptyTitle>
              <EmptyDescription>
                There are no violation records to display. Violations will
                appear here when detected by cameras.
              </EmptyDescription>
            </EmptyHeader>
          </Empty>
        </div>
      ) : (
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
            {violations.map((violation) => {
              const statusVariant = getStatusVariant(violation.status);
              const violationTypes = getViolationTypes(violation);

              return (
                <Card
                  key={violation.id}
                  className="group overflow-hidden p-0 gap-0"
                >
                  <CardHeader className="p-0">
                    <AspectRatio ratio={16 / 9}>
                      <img
                        src={`${import.meta.env.VITE_BASE_API_URL}/storage/${violation.image_path}`}
                        alt={`Violation #${violation.id}`}
                        className="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300 cursor-pointer"
                        onClick={() => {
                          setSelectedViolationForImage(violation);
                          setImageDialogOpen(true);
                        }}
                        onKeyDown={(e) => {
                          if (e.key === "Enter" || e.key === " ") {
                            setSelectedViolationForImage(violation);
                            setImageDialogOpen(true);
                          }
                        }}
                        onError={(e) => {
                          e.currentTarget.src =
                            "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='9' viewBox='0 0 16 9'%3E%3Crect width='16' height='9' fill='%23f1f5f9'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-size='2' fill='%2394a3b8'%3ENo Image%3C/text%3E%3C/svg%3E";
                        }}
                      />
                    </AspectRatio>
                  </CardHeader>
                  <CardContent className="p-4">
                    <div className="flex items-start justify-between mb-3">
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2 mb-1">
                          <Status
                            variant={statusVariant}
                            className="capitalize"
                          >
                            <StatusIndicator />
                            <StatusLabel>{violation.status}</StatusLabel>
                          </Status>
                        </div>
                        <p className="text-sm font-medium truncate">
                          {violation.camera?.name ||
                            `Camera #${violation.camera_id}`}
                        </p>
                      </div>
                      <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                          <Button
                            variant="ghost"
                            size="icon-xs"
                            className="shrink-0"
                          >
                            <MoreHorizontal className="size-4" />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <DropdownMenuItem
                            onClick={() => {
                              setSelectedViolationForImage(violation);
                              setImageDialogOpen(true);
                            }}
                          >
                            View Details
                          </DropdownMenuItem>
                          <DropdownMenuItem
                            onClick={() => {
                              setSelectedViolation(violation);
                              setStatusDialogOpen(true);
                            }}
                          >
                            Update Status
                          </DropdownMenuItem>
                          <DropdownMenuItem
                            variant="destructive"
                            onClick={() => handleDelete(violation)}
                          >
                            Delete
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </div>

                    <div className="space-y-1.5 text-xs">
                      <div className="flex items-center justify-between">
                        <span className="text-muted-foreground">
                          Violation Type:
                        </span>
                        <span className="font-medium truncate ml-2 text-right">
                          {violationTypes}
                        </span>
                      </div>
                      <div className="flex items-center justify-between">
                        <span className="text-muted-foreground">Detected:</span>
                        <span className="font-medium">
                          {formatDate(violation.created_at, {
                            month: "short",
                            day: "numeric",
                            year: "numeric",
                            hour: "2-digit",
                            minute: "2-digit",
                          })}
                        </span>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              );
            })}
          </div>

          {violations.length > 0 && (
            <div className="flex flex-col-reverse items-center justify-between gap-4 sm:flex-row">
              <div className="flex items-center gap-4">
                <span className="whitespace-nowrap text-sm font-medium">
                  Items per page
                </span>
                <Select
                  value={`${per_page}`}
                  onValueChange={(value) => {
                    setPage({ page: 1, per_page: Number(value) });
                  }}
                >
                  <SelectTrigger className="h-8 w-18">
                    <SelectValue placeholder={per_page} />
                  </SelectTrigger>
                  <SelectContent side="top">
                    {[12, 24, 48, 96].map((size) => (
                      <SelectItem key={size} value={`${size}`}>
                        {size}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="flex items-center justify-center gap-4">
                <span className="whitespace-nowrap text-sm font-medium">
                  Page {page} of {totalPages}
                </span>
                <div className="flex items-center space-x-2">
                  <Button
                    aria-label="Go to previous page"
                    variant="outline"
                    size="icon"
                    className="size-8"
                    onClick={() => setPage({ page: page - 1, per_page })}
                    disabled={page <= 1}
                  >
                    <ChevronLeft />
                  </Button>
                  <Button
                    aria-label="Go to next page"
                    variant="outline"
                    size="icon"
                    className="size-8"
                    onClick={() => setPage({ page: page + 1, per_page })}
                    disabled={page >= totalPages}
                  >
                    <ChevronRight />
                  </Button>
                </div>
              </div>
            </div>
          )}
        </>
      )}
      {selectedViolation && (
        <StatusUpdateDialog
          open={statusDialogOpen}
          onOpenChange={setStatusDialogOpen}
          violationId={selectedViolation.id}
          currentStatus={selectedViolation.status}
          currentNotes={selectedViolation.notes}
          onSuccess={handleStatusUpdateSuccess}
        />
      )}
      {selectedViolationForImage && (
        <ImageZoomDialog
          open={imageDialogOpen}
          onOpenChange={setImageDialogOpen}
          image={selectedViolationForImage}
        />
      )}
      {selectedViolationForDelete && (
        <DeleteConfirmDialog
          open={deleteDialogOpen}
          onOpenChange={setDeleteDialogOpen}
          violation={selectedViolationForDelete}
          onSuccess={handleStatusUpdateSuccess}
        />
      )}
    </>
  );
};

export default ViolationHistoryPage;
