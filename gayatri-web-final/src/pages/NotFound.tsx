import { Link } from "react-router-dom";

export default function NotFound() {
  return (
    <div className="min-h-[70vh] flex flex-col items-center justify-center text-center px-6 bg-white pt-32">
      <span className="text-emerald font-heading font-medium text-7xl mb-4">404</span>
      <h1 className="text-2xl md:text-3xl font-heading font-medium text-navy mb-4">Page not found</h1>
      <p className="text-slate mb-8 max-w-md">The page you're looking for doesn't exist or has moved.</p>
      <Link to="/" className="bg-navy text-white px-7 py-3 rounded-full font-medium hover:bg-emerald transition-colors">
        Back to Home
      </Link>
    </div>
  );
}
