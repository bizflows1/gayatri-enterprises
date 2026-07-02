import React from 'react';
import { Link } from 'react-router-dom';
import logoSrc from '../assets/logo.jpeg';

export function LogoMark({ className = 'h-8 w-8' }: { className?: string }) {
  return (
    <span className={`relative inline-block overflow-hidden shrink-0 rounded-lg ${className}`} aria-hidden="true">
      <img
        src={logoSrc}
        alt="GE Mark"
        className="absolute max-w-none"
        style={{ width: '217%', height: '217%', left: '-58.7%', top: '-39.1%' }}
      />
    </span>
  );
}

export function LogoLockup({ 
  className = '', 
  size = 'sm', 
  textColor = 'text-navy', 
  subColor = 'text-slate',
  onClick
}: { 
  className?: string; 
  size?: 'sm' | 'lg'; 
  textColor?: string; 
  subColor?: string;
  onClick?: () => void;
}) {
  return (
    <Link to="/" onClick={onClick} className={`group flex items-center ${size === 'sm' ? 'gap-3' : 'gap-3.5'} ${className}`}>
      <LogoMark className={`shrink-0 rounded-lg ${size === 'sm' ? 'h-11 w-11' : 'h-14 w-14'}`} />
      <span className="leading-tight text-left flex flex-col">
        <span className={`font-heading font-semibold tracking-tight uppercase transition-colors group-hover:text-emerald ${size === 'sm' ? 'text-lg mb-[2px]' : 'text-2xl'} ${textColor}`}>
          GAYATRI
        </span>
        <span className={`font-sans uppercase tracking-widest leading-none ${size === 'sm' ? 'text-xs' : 'text-xs mt-0.5'} ${subColor}`}>
          Enterprises
        </span>
      </span>
    </Link>
  );
}
