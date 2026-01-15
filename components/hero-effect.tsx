'use client';

import React, { useState, useEffect } from "react";
import { TextEffect } from "@/components/ui/text-effect";
import { motion } from "framer-motion";

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
        <div className="flex flex-col items-center justify-center text-center overflow-visible">
            {/* Main Headline Group */}
            <div className="flex flex-col md:flex-row items-baseline justify-center gap-x-6 gap-y-2 mb-4 overflow-visible">
                <div className="overflow-visible flex items-center justify-center">
                    <TextEffect
                        key={`prefix-${key}`}
                        per='word'
                        preset='blur'
                        className="text-white text-5xl md:text-7xl lg:text-9xl font-black tracking-tighter"
                    >
                        {prefixes[prefixIndex]}
                    </TextEffect>
                </div>
                <div className="overflow-visible flex items-center justify-center">
                    <TextEffect
                        key={`suffix-${key}`}
                        per='word'
                        preset='slide'
                        className="text-brand-500 font-black italic text-6xl md:text-8xl lg:text-[10rem] tracking-tighter leading-none drop-shadow-2xl"
                    >
                        {suffixes[suffixIndex]}
                    </TextEffect>
                </div>
            </div>

            {/* Anchor Text - Now part of React for perfect sync */}
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, ease: "easeOut", delay: 0.2 }}
                className="text-white text-4xl md:text-6xl lg:text-7xl font-bold tracking-tight drop-shadow-2xl"
            >
                Dönüştürüyoruz
            </motion.div>
        </div>
    );
}
