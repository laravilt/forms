import { Tabs as UiTabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { resolveIcon } from '@laravilt/support/lib/icons';
import { useEffect, useRef, useState } from 'react';
import Form from '../Form';

export interface TabsProps {
    tabs: Array<any>;
    activeTab?: number;
    persistTabInQueryString?: boolean;
    modelValue?: Record<string, any>;
    onUpdateModelValue?: (value: Record<string, any>) => void;
    [key: string]: any;
}

export default function Tabs({ tabs, activeTab, modelValue, onUpdateModelValue }: TabsProps) {
    const [isLoading, setIsLoading] = useState(false);
    const [currentTab, setCurrentTab] = useState(String(activeTab || 0));
    const loadingTimeout = useRef<ReturnType<typeof setTimeout> | null>(null);

    const handleTabChange = (value: string) => {
        if (value !== currentTab) {
            setIsLoading(true);
            setCurrentTab(value);
            // Small delay to show skeleton
            if (loadingTimeout.current) clearTimeout(loadingTimeout.current);
            loadingTimeout.current = setTimeout(() => {
                setIsLoading(false);
            }, 150);
        }
    };

    // Reactive document direction for RTL support
    const [dir, setDir] = useState<'ltr' | 'rtl'>('ltr');

    useEffect(() => {
        // Set initial direction
        setDir((document.documentElement.dir as 'ltr' | 'rtl') || 'ltr');

        // Watch for direction changes on the html element
        const observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                if (mutation.attributeName === 'dir') {
                    setDir((document.documentElement.dir as 'ltr' | 'rtl') || 'ltr');
                }
            }
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['dir'],
        });

        return () => {
            observer.disconnect();
            if (loadingTimeout.current) clearTimeout(loadingTimeout.current);
        };
    }, []);

    return (
        <UiTabs defaultValue={String(activeTab || 0)} dir={dir} className="w-full" onValueChange={handleTabChange}>
            <TabsList className="w-full justify-start">
                {(tabs || []).map((tab: any, index: number) => {
                    const TabIcon = tab.icon ? resolveIcon(tab.icon) : null;
                    return (
                        <TabsTrigger key={index} value={String(index)} className="gap-2">
                            {tab.icon && TabIcon && <TabIcon className="h-4 w-4" />}
                            <span>{tab.label}</span>
                            {tab.badge && (
                                <span className="ms-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium rounded-full bg-primary/10 text-primary">
                                    {tab.badge}
                                </span>
                            )}
                        </TabsTrigger>
                    );
                })}
            </TabsList>

            {(tabs || []).map((tab: any, index: number) => (
                <TabsContent key={index} value={String(index)} className="space-y-6 mt-6">
                    {/* Skeleton while loading */}
                    {isLoading && currentTab === String(index) ? (
                        <div key="skeleton" className="space-y-6 transition-opacity duration-150 ease-out animate-in fade-in-0">
                            <div className="bg-card rounded-xl border shadow-sm p-6 space-y-4">
                                <div className="h-4 bg-muted/60 rounded w-1/4 animate-pulse"></div>
                                <div className="space-y-3">
                                    <div className="h-10 bg-muted/60 rounded animate-pulse" style={{ animationDelay: '0ms' }}></div>
                                    <div className="h-10 bg-muted/60 rounded animate-pulse" style={{ animationDelay: '75ms' }}></div>
                                    <div className="h-10 bg-muted/60 rounded w-3/4 animate-pulse" style={{ animationDelay: '150ms' }}></div>
                                </div>
                            </div>
                        </div>
                    ) : tab.schema ? (
                        /* Actual content */
                        <Form
                            key="content"
                            schema={tab.schema}
                            modelValue={modelValue}
                            onUpdateModelValue={(value) => onUpdateModelValue?.(value)}
                        />
                    ) : null}
                </TabsContent>
            ))}
        </UiTabs>
    );
}
