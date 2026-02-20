import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { cn } from "@/lib/utils";

interface LastDetectedProps {
	timestamp?: string;
	image_url?: string;
	violation_type?: string;
}

export function LastDetected({
	timestamp,
	image_url,
	violation_type,
}: LastDetectedProps) {
	const formattedTimestamp = timestamp
		? new Date(timestamp).toLocaleString()
		: null;

	const hasViolation = timestamp && image_url && violation_type;

	return (
		<Card className="w-full">
			<CardHeader>
				<CardTitle className="text-sm font-medium">
					{hasViolation ? "Last Detected" : "No Violations Detected Yet"}
				</CardTitle>
			</CardHeader>
			<CardContent>
				{hasViolation ? (
					<div className="flex flex-col gap-4">
						<div className="relative overflow-hidden rounded-lg border bg-muted/20">
							<img
								src={image_url}
								alt={`Violation: ${violation_type}`}
								className={cn(
									"w-full h-auto object-cover transition-transform duration-200",
									"hover:scale-105",
								)}
							/>
						</div>

						<div className="flex items-center justify-between gap-2">
							<Badge variant="destructive" className="capitalize">
								{violation_type}
							</Badge>

							{formattedTimestamp && (
								<span className="text-xs text-muted-foreground">
									{formattedTimestamp}
								</span>
							)}
						</div>
					</div>
				) : (
					<div className="flex items-center justify-center py-8">
						<p className="text-sm text-muted-foreground">
							Awaiting detection data...
						</p>
					</div>
				)}
			</CardContent>
		</Card>
	);
}
