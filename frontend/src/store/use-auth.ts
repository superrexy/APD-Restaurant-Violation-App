import { create } from "zustand";
import { createJSONStorage, persist } from "zustand/middleware";
import type { User } from "@/api/rest/client";

interface AuthState {
	token: string | null;
	isAuthenticated: boolean;
	user: User | null;
}

interface AuthActions {
	setAuth: (token: string, user?: User) => void;
	setUser: (user: User) => void;
	logout: () => void;
	setIsAuthenticated: (value: boolean) => void;
}

type AuthStore = AuthState & AuthActions;

export const useAuthStore = create<AuthStore>()(
	persist(
		(set) => ({
			token: null,
			isAuthenticated: false,
			user: null,

			setAuth: (token: string, user?: User) =>
				set({ token, isAuthenticated: true, user: user ?? null }),

			setUser: (user: User) => set({ user }),

			logout: () => set({ token: null, isAuthenticated: false, user: null }),

			setIsAuthenticated: (value: boolean) => set({ isAuthenticated: value }),
		}),
		{
			name: "auth-storage",
			storage: createJSONStorage(() => localStorage),
			partialize: (state) => ({
				token: state.token,
				isAuthenticated: state.isAuthenticated,
				user: state.user,
			}),
		},
	),
);
