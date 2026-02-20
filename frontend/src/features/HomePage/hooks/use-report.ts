import { useState } from "react";

export const useReport = () => {
	const [isReportRunning, setIsReportRunning] = useState(false);
	const [batteryReport, setBatteryReport] = useState({
		total_battery: 0,
		total_good: 0,
		total_not_good: 0,
	});



	return {
		isReportRunning,
		setIsReportRunning,
		batteryReport,
		setBatteryReport,
	};
};
