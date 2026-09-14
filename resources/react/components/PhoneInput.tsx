import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { useLatest } from '@laravilt/support/composables/hooks';
import { Check, ChevronsUpDown, Search } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';

export interface PhoneInputProps {
    name?: string;
    value?: string | null;
    modelValue?: string | null;
    placeholder?: string;
    disabled?: boolean;
    readonly?: boolean;
    onUpdateModelValue?: (value: string | null) => void;
    onUpdateValue?: (value: string | null) => void;
}

// Popular country codes with flags
const countryCodes = [
    { code: '+1', country: 'US', flag: '🇺🇸', name: 'United States' },
    { code: '+1', country: 'CA', flag: '🇨🇦', name: 'Canada' },
    { code: '+44', country: 'GB', flag: '🇬🇧', name: 'United Kingdom' },
    { code: '+61', country: 'AU', flag: '🇦🇺', name: 'Australia' },
    { code: '+86', country: 'CN', flag: '🇨🇳', name: 'China' },
    { code: '+33', country: 'FR', flag: '🇫🇷', name: 'France' },
    { code: '+49', country: 'DE', flag: '🇩🇪', name: 'Germany' },
    { code: '+91', country: 'IN', flag: '🇮🇳', name: 'India' },
    { code: '+81', country: 'JP', flag: '🇯🇵', name: 'Japan' },
    { code: '+82', country: 'KR', flag: '🇰🇷', name: 'South Korea' },
    { code: '+52', country: 'MX', flag: '🇲🇽', name: 'Mexico' },
    { code: '+7', country: 'RU', flag: '🇷🇺', name: 'Russia' },
    { code: '+966', country: 'SA', flag: '🇸🇦', name: 'Saudi Arabia' },
    { code: '+27', country: 'ZA', flag: '🇿🇦', name: 'South Africa' },
    { code: '+34', country: 'ES', flag: '🇪🇸', name: 'Spain' },
    { code: '+971', country: 'AE', flag: '🇦🇪', name: 'UAE' },
    { code: '+20', country: 'EG', flag: '🇪🇬', name: 'Egypt' },
];

// Parse initial value if provided
const parseInitial = (initialValue: string): { code: string; phone: string } => {
    if (initialValue) {
        const match = initialValue.match(/^(\+\d+)\s*(.*)$/);
        if (match) {
            return { code: match[1], phone: match[2] };
        }
        return { code: '+1', phone: initialValue };
    }
    return { code: '+1', phone: '' };
};

export default function PhoneInput({
    name,
    value = null,
    modelValue = null,
    placeholder = 'Phone number',
    disabled,
    readonly,
    onUpdateModelValue,
    onUpdateValue,
}: PhoneInputProps) {
    const [initial] = useState(() => parseInitial(modelValue || value || ''));
    const [selectedCountryCode, setSelectedCountryCode] = useState(initial.code);
    const [phoneNumber, setPhoneNumber] = useState(initial.phone);
    const [isCountrySelectorOpen, setIsCountrySelectorOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const isUserTyping = useRef(false);

    // Filtered countries based on search
    const filteredCountries = useMemo(() => {
        if (!searchQuery) return countryCodes;
        const query = searchQuery.toLowerCase();
        return countryCodes.filter(
            (country) =>
                country.name.toLowerCase().includes(query) ||
                country.code.includes(query) ||
                country.country.toLowerCase().includes(query),
        );
    }, [searchQuery]);

    // Watch for prop changes from parent (but not during user typing)
    const incoming = modelValue || value;
    const previousIncoming = useRef(incoming);
    useEffect(() => {
        if (previousIncoming.current === incoming) return;
        previousIncoming.current = incoming;

        if (isUserTyping.current) return;

        if (!incoming) {
            setPhoneNumber('');
            return;
        }
        const match = incoming.match(/^(\+\d+)\s*(.*)$/);
        if (match) {
            setSelectedCountryCode(match[1]);
            setPhoneNumber(match[2]);
        } else {
            setPhoneNumber(incoming);
        }
    }, [incoming]);

    const buildFullPhoneNumber = (code: string, phone: string): string | null => {
        if (!phone) return null;
        return `${code} ${phone}`;
    };

    const fullPhoneNumber = buildFullPhoneNumber(selectedCountryCode, phoneNumber);

    const emitters = useLatest({ onUpdateModelValue, onUpdateValue, selectedCountryCode });

    const updateCountryCode = (code: string) => {
        setSelectedCountryCode(code);
        setIsCountrySelectorOpen(false);
        setSearchQuery('');
        // Emit the updated value
        const full = buildFullPhoneNumber(code, phoneNumber);
        onUpdateModelValue?.(full);
        onUpdateValue?.(full);
    };

    // Watch for phoneNumber changes and emit
    const previousPhoneNumber = useRef(phoneNumber);
    useEffect(() => {
        if (previousPhoneNumber.current === phoneNumber) return;
        previousPhoneNumber.current = phoneNumber;

        isUserTyping.current = true;
        // Emit the updated value
        const full = buildFullPhoneNumber(emitters.current.selectedCountryCode, phoneNumber);
        emitters.current.onUpdateModelValue?.(full);
        emitters.current.onUpdateValue?.(full);
        // Clear flag after a short delay (after the debounce completes in parent)
        // The parent (TextInput) has 300ms debounce for live fields
        setTimeout(() => {
            isUserTyping.current = false;
        }, 400);
    }, [phoneNumber, emitters]);

    return (
        <div className="relative">
            {/* Country Code Selector (inside input as prefix) */}
            <div className="absolute inset-y-0 left-0 flex items-center z-10">
                <Popover open={isCountrySelectorOpen} onOpenChange={setIsCountrySelectorOpen}>
                    <PopoverTrigger asChild>
                        <Button
                            variant="ghost"
                            role="combobox"
                            aria-expanded={isCountrySelectorOpen}
                            className="h-full border-0 bg-transparent hover:bg-accent focus:ring-0 focus-visible:ring-0 rounded-r-none px-2 gap-1"
                        >
                            <span className="text-base">{countryCodes.find((c) => c.code === selectedCountryCode)?.flag}</span>
                            <span className="text-sm font-medium">{selectedCountryCode}</span>
                            <ChevronsUpDown className="h-3 w-3 opacity-50" />
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-[280px] p-0" align="start">
                        <div className="flex flex-col">
                            {/* Search input */}
                            <div className="p-3 border-b border-border">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        value={searchQuery}
                                        onChange={(e) => setSearchQuery(e.target.value)}
                                        type="text"
                                        placeholder="Search country..."
                                        className="pl-9 h-9"
                                    />
                                </div>
                            </div>

                            {/* Countries list */}
                            <div className="max-h-[300px] overflow-y-auto">
                                {filteredCountries.length === 0 ? (
                                    <div className="p-4 text-center text-sm text-muted-foreground">No country found.</div>
                                ) : (
                                    filteredCountries.map((country) => (
                                        <button
                                            key={country.country}
                                            type="button"
                                            className={cn(
                                                'w-full flex items-center gap-2 px-3 py-2 hover:bg-accent transition-colors text-left',
                                                { 'bg-accent': selectedCountryCode === country.code },
                                            )}
                                            onClick={() => updateCountryCode(country.code)}
                                        >
                                            <Check
                                                className={cn('h-4 w-4', {
                                                    'opacity-100': selectedCountryCode === country.code,
                                                    'opacity-0': selectedCountryCode !== country.code,
                                                })}
                                            />
                                            <span className="text-lg">{country.flag}</span>
                                            <span className="flex-1">{country.name}</span>
                                            <span className="text-sm text-muted-foreground">{country.code}</span>
                                        </button>
                                    ))
                                )}
                            </div>
                        </div>
                    </PopoverContent>
                </Popover>
            </div>

            {/* Phone Number Input */}
            <Input
                type="tel"
                value={phoneNumber}
                onChange={(e) => setPhoneNumber(e.target.value)}
                placeholder={placeholder}
                disabled={disabled}
                readOnly={readonly}
                className="pl-[110px]"
            />

            {/* Hidden input for full phone number */}
            {name && <input type="hidden" name={name} value={fullPhoneNumber || ''} />}
        </div>
    );
}
