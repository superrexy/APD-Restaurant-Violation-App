import { IconWifi, IconWifiOff } from "@tabler/icons-react";
import { useMemo } from "react";
import { AspectRatio } from "../../components/ui/aspect-ratio";
import { useHealthPolling } from "../../hooks/use-health-polling";
import { useViolationsPolling } from "../../hooks/use-violations-polling";

const HomePage = () => {
  const { violations } = useViolationsPolling({ pollingInterval: 3000 });

  const { status } = useHealthPolling();

  const cameraStatus = status?.camera_status;

  const lastViolation = useMemo(() => {
    if (violations.length === 0) {
      return null;
    }

    const violation = violations[0];
    const firstDetail = violation.violation_details?.[0];

    if (!violation.camera) {
      return null;
    }

    return {
      violation_id: violation.id,
      camera_code: violation.camera.code,
      violation_type: firstDetail?.violation_type?.name || "Unknown",
      image_path: violation.image_path,
      detected_at: violation.created_at,
    };
  }, [violations]);

  return (
    <>
      <h1 className="text-2xl font-bold">Restaurant Violation Monitoring</h1>
      <div className="flex flex-wrap xl:flex-nowrap xl:space-x-5 justify-between mt-4 space-y-5 xl:space-y-0">
        <div className="2xl:w-[1000px] w-full xl:w-1/2">
          <AspectRatio ratio={16 / 9}>
            {!cameraStatus ? (
              <div className="flex items-center justify-center w-full h-full bg-gray-300 text-gray-600 rounded-xl dark:bg-card dark:text-muted-foreground border">
                Camera is off
              </div>
            ) : (
              <img
                className="h-full w-full rounded-xl bg-accent"
                src={`${import.meta.env.VITE_YOLO_SERVICE_URL}/stream`}
                alt="Camera feed"
              ></img>
            )}
          </AspectRatio>
        </div>
        <div className="flex flex-col gap-5 w-full xl:w-1/2 2xl:w-[350px]">
          <div className="rounded-xl border bg-card text-card-foreground shadow h-min">
            <div className="flex flex-col space-y-1.5 p-6 pb-2">
              <div className="font-semibold leading-none tracking-tight">
                Status Connection
              </div>
              <div className="text-sm text-muted-foreground">
                Server connection status
              </div>
            </div>

            <div className="p-6 pt-0 grid gap-2">
              <div className="flex items-center justify-between">
                <div
                  data-testid="camera-status"
                  className={`flex w-full items-center gap-2 px-3 py-2 rounded-md ${status?.camera_status ? "bg-green-100" : "bg-red-100"}`}
                >
                  {status?.camera_status && (
                    <IconWifi className="w-5 h-5 text-green-600" />
                  )}
                  {!status?.camera_status && (
                    <IconWifiOff className="w-5 h-5 text-red-600" />
                  )}
                  <span
                    className={`font-medium ${status?.camera_status ? "text-green-600" : "text-red-600"}`}
                  >
                    {status?.camera_status ? "Camera Online" : "Camera Offline"}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div className="rounded-xl border bg-card text-card-foreground shadow h-min">
            <div className="flex flex-col space-y-1.5 p-6 pb-2">
              <div className="font-semibold leading-none tracking-tight">
                Last Detected
              </div>
              <div className="text-sm text-muted-foreground">
                Most recent violation
              </div>
            </div>

            <div className="p-6 pt-0">
              {lastViolation ? (
                <div className="space-y-4">
                  <div className="aspect-video w-full overflow-hidden rounded-lg bg-muted">
                    <img
                      src={`${import.meta.env.VITE_BASE_API_URL}/storage/${lastViolation.image_path}`}
                      alt={`Violation at ${lastViolation.detected_at}`}
                      className="h-full w-full object-cover"
                    />
                  </div>
                  <div className="space-y-2">
                    <div className="flex justify-between text-sm">
                      <span className="text-muted-foreground">Type</span>
                      <span className="font-medium">
                        {lastViolation.violation_type}
                      </span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span className="text-muted-foreground">Camera</span>
                      <span className="font-medium">
                        {lastViolation.camera_code}
                      </span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span className="text-muted-foreground">Detected</span>
                      <span className="font-medium">
                        {new Date(lastViolation.detected_at).toLocaleString()}
                      </span>
                    </div>
                  </div>
                </div>
              ) : (
                <div className="flex h-48 items-center justify-center text-sm text-muted-foreground">
                  No violations detected yet
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default HomePage;
