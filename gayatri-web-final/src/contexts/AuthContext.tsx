import { createContext, useContext, useEffect, useState, type ReactNode } from "react";
import { api, ApiError } from "../lib/api";

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
}

interface Client {
  id: number;
  company_name: string;
  gstin: string | null;
  credit_limit: string;
  outstanding_balance: string;
  status: string;
}

interface AuthState {
  user: User | null;
  client: Client | null;
  loading: boolean;
  register: (data: { name: string; email: string; password: string; company_name: string; gstin?: string; phone?: string }) => Promise<void>;
  login: (data: { email: string; password: string }) => Promise<void>;
  logout: () => Promise<void>;
}

const AuthContext = createContext<AuthState | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [client, setClient] = useState<Client | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api
      .get<{ user: User; client: Client }>("/api/auth/me")
      .then((res) => {
        setUser(res.user);
        setClient(res.client);
      })
      .catch(() => {
        // not logged in — fine, leave user/client null
      })
      .finally(() => setLoading(false));
  }, []);

  async function register(data: { name: string; email: string; password: string; company_name: string; gstin?: string; phone?: string }) {
    const res = await api.post<{ user: User; client: Client }>("/api/auth/register", data);
    setUser(res.user);
    setClient(res.client);
  }

  async function login(data: { email: string; password: string }) {
    const res = await api.post<{ user: User; client: Client }>("/api/auth/login", data);
    setUser(res.user);
    setClient(res.client);
  }

  async function logout() {
    await api.post("/api/auth/logout");
    setUser(null);
    setClient(null);
  }

  return (
    <AuthContext.Provider value={{ user, client, loading, register, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthState {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}

export { ApiError };
