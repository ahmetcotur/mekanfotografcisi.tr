'use client';

import React, { useState, useEffect } from "react";
import { TextEffect } from "@/components/ui/text-effect";

export function HeroEffect() {
    const [prefixIndex, setPrefixIndex] = useState(0);
    const [suffixIndex, setSuffixIndex] = useState(0);
    const [key, setKey] = useState(0);

    const prefixes = ["Mekanınızı", "Otelinizi", "Restoranınızı", "Villanızı", "Ofisinizi"];
    const suffixes = ["Sanata", "Markaya", "Satışa", "Hikayeye", "Prestije"];

    useEffect(() => {
        const interval = setInterval(() => {
            setPrefixIndex((prev) => (prev + 1) % prefixes.length);
            setSuffixIndex((prev) => (prev + 1) % suffixes.length);
            setKey(prev => prev + 1);
        }, 4000);
        return () => clearInterval(interval);
    }, []);

    return (
        <div className="flex flex-col md:flex-row items-center justify-center gap-x-4 gap-y-2 mb-8 overflow-visible">
            <div className="overflow-visible flex items-center justify-center">
                <TextEffect
                    key={`prefix-${key}`}
                    per='word'
                    preset='blur'
                    className="text-white text-5xl md:text-7xl lg:text-8xl font-black tracking-tight"
                >
                    {prefixes[prefixIndex]}
                </TextEffect>
            </div>
            <div className="overflow-visible flex items-center justify-center">
                <TextEffect
                    key={`suffix-${key}`}
                    per='word'
                    preset='blur'
                    className="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-brand-300 font-black italic text-5xl md:text-7xl lg:text-8xl tracking-tight"
                >
                    {suffixes[suffixIndex]}
                </TextEffect>
            </div>
        </div>
    );
}
