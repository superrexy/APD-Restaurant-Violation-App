import { Battery } from "lucide-react";
import { Separator } from "../../../components/ui/separator";

interface BatteryCellInfoProps {
	batteryCells?: boolean[];
	totalObjects?: number;
}

const BatteryCellInfo = ({
	batteryCells = [false, false, false, false, false, false],
	totalObjects = 0,
}: BatteryCellInfoProps) => {
	// State untuk status battery cells (true = normal/hijau, false = error/merah)

	return (
		<div className="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
			<div className="flex items-center justify-between mb-3">
				<div className="flex items-center">
					<Battery className="w-5 h-5 mr-2" />
					<span className="font-medium text-sm">Battery Status</span>
				</div>
			</div>

			{/* Battery Container */}
			<div className="bg-white dark:bg-gray-700 p-3 rounded-lg border-2 border-gray-300 dark:border-gray-600">
				{/* Battery Cells */}
				{batteryCells.length > 0 ? (
					<div className="grid grid-cols-6 gap-1">
						{batteryCells.map((isNormal, index) => (
							<div
								key={`cell-${isNormal ? "normal" : "error"}-${index}`}
								className={`
                flex-1 h-10 rounded-sm border-2 transition-all duration-200 hover:opacity-80
                ${
									isNormal
										? "bg-green-500 border-green-600"
										: "bg-red-500 border-red-600"
								}
              `}
							>
								<div className="flex items-center justify-center h-full">
									<span className="text-white font-bold text-sm">
										{index + 1}
									</span>
								</div>
							</div>
						))}
					</div>
				) : (
					<div className="flex items-center justify-center h-full w-full">
						<span className="text-muted-foreground text-sm">
							No cells detected
						</span>
					</div>
				)}

				{/* Battery Labels */}
				{batteryCells.length > 0 && (
					<div className="flex justify-between mt-2 text-xs text-muted-foreground">
						<span>Cell 1</span>
						<span>Cell 2</span>
						<span>Cell 3</span>
						<span>Cell 4</span>
						<span>Cell 5</span>
						<span>Cell 6</span>
					</div>
				)}
			</div>

			{/* Status Summary */}
			<div className="mt-3 space-y-2">
				<div className="flex justify-between text-xs">
					<div className="flex items-center">
						<div className="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
						<span>Normal: {batteryCells.filter(Boolean).length}</span>
					</div>
					<div className="flex items-center">
						<div className="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
						<span>Error: {batteryCells.filter((cell) => !cell).length}</span>
					</div>
				</div>
			</div>

			<Separator className="my-3" />
			<div className="flex items-center justify-center text-xs">
				<div className="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
				<span>Total Objects: {totalObjects}</span>
			</div>
		</div>
	);
};

export default BatteryCellInfo;
