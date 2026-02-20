import * as React from "react";

export interface UseAlertDialogOptions {
	title?: string;
	description?: string;
	cancelText?: string;
	confirmText?: string;
	onConfirm?: () => void | Promise<void>;
	onCancel?: () => void;
}

export function useAlertDialog(options: UseAlertDialogOptions = {}) {
	const [open, setOpen] = React.useState(false);

	const {
		title = "Are you absolutely sure?",
		description = "This action cannot be undone.",
		cancelText = "Cancel",
		confirmText = "Continue",
		onConfirm,
		onCancel,
	} = options;

	const handleOpenChange = React.useCallback(
		(newOpen: boolean) => {
			setOpen(newOpen);
			if (!newOpen && onCancel) {
				onCancel();
			}
		},
		[onCancel],
	);

	const handleConfirm = React.useCallback(async () => {
		if (onConfirm) {
			await onConfirm();
		}
		setOpen(false);
	}, [onConfirm]);

	const openDialog = React.useCallback(() => {
		setOpen(true);
	}, []);

	const closeDialog = React.useCallback(() => {
		setOpen(false);
	}, []);

	return {
		open,
		title,
		description,
		cancelText,
		confirmText,
		onOpenChange: handleOpenChange,
		onConfirm: handleConfirm,
		openDialog,
		closeDialog,
	};
}
