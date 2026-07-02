import { Component, type ReactNode } from "react";

interface Props {
  children: ReactNode;
}

interface State {
  error: Error | null;
}

// Catches render-time crashes anywhere in the tree (a bad API response, a
// null-pointer in one page's code, etc.) so the *entire* site doesn't go
// blank-white for the visitor — just shows a recoverable error screen with a
// reload button instead of a dead tab.
export default class ErrorBoundary extends Component<Props, State> {
  state: State = { error: null };

  static getDerivedStateFromError(error: Error): State {
    return { error };
  }

  componentDidCatch(error: Error, info: { componentStack?: string }) {
    console.error("Unhandled UI error:", error, info.componentStack);
  }

  render() {
    if (this.state.error) {
      return (
        <div className="min-h-screen flex flex-col items-center justify-center text-center px-6 bg-white">
          <h1 className="text-2xl font-heading font-medium text-navy mb-4">Something went wrong</h1>
          <p className="text-slate mb-8 max-w-md">
            This page hit an unexpected error. Reloading usually fixes it.
          </p>
          <button
            onClick={() => window.location.reload()}
            className="bg-navy text-white px-7 py-3 rounded-full font-medium hover:bg-emerald transition-colors"
          >
            Reload page
          </button>
        </div>
      );
    }
    return this.props.children;
  }
}
