<?php

namespace Laravilt\Forms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravilt\Forms\Components\Builder;
use Laravilt\Forms\Components\Builder\Block;
use Laravilt\Forms\Components\Checkbox;
use Laravilt\Forms\Components\CodeEditor;
use Laravilt\Forms\Components\ColorPicker;
use Laravilt\Forms\Components\DatePicker;
use Laravilt\Forms\Components\DateRangePicker;
use Laravilt\Forms\Components\DateTimePicker;
use Laravilt\Forms\Components\FileUpload;
use Laravilt\Forms\Components\IconPicker;
use Laravilt\Forms\Components\KeyValue;
use Laravilt\Forms\Components\MarkdownEditor;
use Laravilt\Forms\Components\NumberField;
use Laravilt\Forms\Components\PinInput;
use Laravilt\Forms\Components\Radio;
use Laravilt\Forms\Components\RateInput;
use Laravilt\Forms\Components\Repeater;
use Laravilt\Forms\Components\RichEditor;
use Laravilt\Forms\Components\Select;
use Laravilt\Forms\Components\Slider;
use Laravilt\Forms\Components\TagsInput;
use Laravilt\Forms\Components\Textarea;
use Laravilt\Forms\Components\TextInput;
use Laravilt\Forms\Components\TimePicker;
use Laravilt\Forms\Components\Toggle;
use Laravilt\Forms\Components\ToggleButtons;
use Laravilt\Forms\Services\FormValidator;
use Laravilt\Schemas\Components\Grid;
use Laravilt\Schemas\Components\Section;
use Laravilt\Schemas\Components\Tab;
use Laravilt\Schemas\Components\Tabs;
use Laravilt\Schemas\Schema;

class FormsDemoController extends Controller
{
    public function index()
    {
        // Get the form schema
        $formSchema = $this->getFormSchema();

        // Serialize the schema to array format for Vue
        $schema = $formSchema->getSchema();
        $serializedSchema = array_map(fn ($component) => $component->toLaraviltProps(), $schema);

        return inertia('forms/Demo', [
            'formSchema' => $serializedSchema,
            'auth' => [
                'user' => [
                    'id' => 1,
                    'name' => 'Demo User',
                    'email' => 'demo@example.com',
                ],
            ],
        ]);
    }

    public function submit(Request $request)
    {
        // Get the form schema (same as in index method)
        $formSchema = $this->getFormSchema();

        // Extract validation rules from the schema
        $validation = FormValidator::getRules($formSchema);

        // Validate the request using schema-generated rules
        $validated = $request->validate(
            $validation['rules'],
            $validation['messages']
        );

        return redirect()
            ->back()
            ->with('success', 'Form submitted successfully!')
            ->with('data', $validated);
    }

    /**
     * Blade demo page
     */
    public function blade()
    {
        $formSchema = $this->getFormSchema();

        return view('laravilt-forms::demo.blade', [
            'formSchema' => $formSchema,
        ])->with('errors', session()->get('errors', new \Illuminate\Support\MessageBag));
    }

    /**
     * Handle Blade form submission
     */
    public function bladeSubmit(Request $request)
    {
        // Get the form schema
        $formSchema = $this->getFormSchema();

        // Extract validation rules from the schema
        $validation = FormValidator::getRules($formSchema);

        // Validate the request using schema-generated rules
        $validated = $request->validate(
            $validation['rules'],
            $validation['messages']
        );

        // Check if it's a Laravilt AJAX request
        if ($request->header('X-Laravilt')) {
            // Return the same blade view with success message for Laravilt
            return view('laravilt-forms::demo.blade', [
                'formSchema' => $formSchema,
            ])->with('success', 'Form submitted successfully!')
                ->with('data', $validated)
                ->with('errors', new \Illuminate\Support\MessageBag);
        }

        // Regular form submission fallback
        return redirect()
            ->back()
            ->with('success', 'Form submitted successfully!')
            ->with('data', $validated);
    }

    /**
     * Get the form schema (extracted to reuse in both index and submit)
     */
    protected function getFormSchema(): Schema
    {
        return $this->buildDemoFormSchema();
    }

    /**
     * Build the comprehensive demo form schema
     */
    protected function buildDemoFormSchema(): Schema
    {
        return Schema::make('demo_form')
            ->schema([
                // Tabs Layout
                Tabs::make('main_tabs')
                    ->tabs([
                        // Tab 1: Personal Information with Sections
                        Tab::make('personal_tab')
                            ->label('Personal Info')
                            ->icon('user')
                            ->badge('3')
                            ->schema([
                                Section::make('basic_info_section')
                                    ->heading('Basic Information')
                                    ->description('Enter your personal details')
//                                    ->collapsible()
//                                    ->collapsed()
                                    ->icon('id-card')
                                    ->schema([
                                        Grid::make('basic_info_grid')
                                            ->columns(2)
                                            ->schema([
                                                TextInput::make('first_name')
                                                    ->label('First Name')
                                                    ->placeholder('John')
                                                    ->required()
                                                    ->autofocus(),

                                                TextInput::make('last_name')
                                                    ->label('Last Name')
                                                    ->placeholder('Doe')
                                                    ->required(),

                                                TextInput::make('email')
                                                    ->label('Email Address')
                                                    ->email()
                                                    ->required()
                                                    ->placeholder('john@example.com')
                                                    ->columnSpan(2),

                                                TextInput::make('phone')
                                                    ->tel()
                                                    ->label('Phone Number')
                                                    ->placeholder('+1 (555) 000-0000'),

                                                DatePicker::make('birthdate')
                                                    ->label('Date of Birth')
                                                    ->required(),

                                                DateRangePicker::make('vacation_dates')
                                                    ->label('Vacation Dates')
                                                    ->helperText('Select your vacation date range')
                                                    ->numberOfMonths(2)
                                                    ->columnSpan(2),

                                                DateTimePicker::make('appointment')
                                                    ->label('Appointment Date & Time')
                                                    ->helperText('Schedule your appointment')
                                                    ->required(),

                                                TimePicker::make('preferred_time')
                                                    ->label('Preferred Contact Time')
                                                    ->helperText('Best time to contact you'),
                                            ]),
                                    ]),

                                Section::make('date_time_section')
                                    ->heading('Date & Time Pickers Testing')
                                    ->description('Test all date and time picker variations')
                                    ->icon('calendar')
                                    ->schema([
                                        Grid::make('date_time_grid')
                                            ->columns(2)
                                            ->schema([
                                                DatePicker::make('test_date_empty')
                                                    ->label('Date Picker (Empty)')
                                                    ->helperText('Test with no initial value'),

                                                DatePicker::make('test_date_prefilled')
                                                    ->label('Date Picker (Prefilled)')
                                                    ->helperText('Test with initial value')
                                                    ->default('2024-06-15'),

                                                DateTimePicker::make('test_datetime_empty')
                                                    ->label('DateTime Picker (Empty)')
                                                    ->helperText('Test with no initial value'),

                                                DateTimePicker::make('test_datetime_prefilled')
                                                    ->label('DateTime Picker (Prefilled)')
                                                    ->helperText('Test with initial value')
                                                    ->default('2024-06-15 14:30'),

                                                DateRangePicker::make('test_range_empty')
                                                    ->label('Date Range Picker (Empty)')
                                                    ->helperText('Test with no initial value')
                                                    ->columnSpan(2),

                                                DateRangePicker::make('test_range_prefilled')
                                                    ->label('Date Range Picker (Prefilled)')
                                                    ->helperText('Test with initial value')
                                                    ->default([
                                                        'start' => '2024-06-01',
                                                        'end' => '2024-06-15',
                                                    ])
                                                    ->columnSpan(2),

                                                TimePicker::make('test_time_empty')
                                                    ->label('Time Picker (Empty)')
                                                    ->helperText('Test with no initial value'),

                                                TimePicker::make('test_time_prefilled')
                                                    ->label('Time Picker (Prefilled)')
                                                    ->helperText('Test with initial value')
                                                    ->default('14:30'),
                                            ]),
                                    ]),

                                Section::make('address_section')
                                    ->heading('Address')
                                    ->description('Your residential address')
                                    ->icon('map-pin')
//                                    ->collapsible()
//                                    ->collapsed()
                                    ->schema([
                                        Grid::make('address_grid')
                                            ->columns(2)
                                            ->schema([
                                                TextInput::make('street')
                                                    ->label('Street Address')
                                                    ->columnSpan(2),

                                                TextInput::make('city')
                                                    ->label('City')
                                                    ->required(),

                                                TextInput::make('state')
                                                    ->label('State / Province')
                                                    ->required(),

                                                TextInput::make('zip')
                                                    ->label('ZIP / Postal Code')
                                                    ->required(),

                                                Select::make('country')
                                                    ->label('Country')
                                                    ->options([
                                                        'us' => 'United States',
                                                        'ca' => 'Canada',
                                                        'uk' => 'United Kingdom',
                                                        'au' => 'Australia',
                                                    ])
                                                    ->required(),
                                            ]),
                                    ]),

                                Section::make('profile_section')
                                    ->heading('Profile Details')
                                    ->description('Tell us more about yourself')
                                    ->icon('user-circle')
//                                    ->collapsible()
//                                    ->collapsed()
                                    ->schema([
                                        Textarea::make('bio')
                                            ->label('Biography')
                                            ->placeholder('Tell us about yourself...')
                                            ->rows(4)
                                            ->maxLength(500)
                                            ->showCharacterCount()
                                            ->showWordCount(),

                                        FileUpload::make('avatar')
                                            ->label('Profile Avatar')
                                            ->avatar()
                                            ->maxSize(2048),

                                        Grid::make('profile_grid')
                                            ->columns(2)
                                            ->schema([
                                                TextInput::make('website')
                                                    ->label('Website')
                                                    ->url()
                                                    ->placeholder('https://example.com'),

                                                Select::make('language')
                                                    ->label('Preferred Language')
                                                    ->searchable()
                                                    ->options([
                                                        'en' => 'English',
                                                        'es' => 'Spanish',
                                                        'fr' => 'French',
                                                        'de' => 'German',
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // Tab 2: Professional Information
                        Tab::make('professional_tab')
                            ->label('Professional')
                            ->icon('briefcase')
                            ->schema([
                                Section::make('work_section')
                                    ->heading('Work Experience')
                                    ->description('Your professional background')
//                                    ->collapsible()
//                                    ->collapsed()
                                    ->schema([
                                        Grid::make('work_grid')
                                            ->columns(2)
                                            ->schema([
                                                TextInput::make('company')
                                                    ->label('Company Name')
                                                    ->required()
                                                    ->columnSpan(2),

                                                TextInput::make('position')
                                                    ->label('Position')
                                                    ->required(),

                                                Select::make('industry')
                                                    ->label('Industry')
                                                    ->options([
                                                        'tech' => 'Technology',
                                                        'finance' => 'Finance',
                                                        'healthcare' => 'Healthcare',
                                                        'education' => 'Education',
                                                    ])
                                                    ->required(),

                                                Radio::make('experience')
                                                    ->label('Experience Level')
                                                    ->options([
                                                        'beginner' => 'Beginner',
                                                        'intermediate' => 'Intermediate',
                                                        'advanced' => 'Advanced',
                                                        'expert' => 'Expert',
                                                    ])
                                                    ->descriptions([
                                                        'beginner' => '0-1 years',
                                                        'intermediate' => '1-3 years',
                                                        'advanced' => '3-5 years',
                                                        'expert' => '5+ years',
                                                    ])
                                                    ->columnSpan(2),
                                            ]),
                                    ]),

                                Section::make('skills_section')
                                    ->heading('Skills & Expertise')
                                    ->description('What are you good at?')
//                                    ->collapsible()
//                                    ->collapsed()
                                    ->schema([
                                        Select::make('skills')
                                            ->label('Technical Skills')
                                            ->multiple()
                                            ->options([
                                                'php' => 'PHP',
                                                'js' => 'JavaScript',
                                                'python' => 'Python',
                                                'java' => 'Java',
                                                'ruby' => 'Ruby',
                                                'go' => 'Go',
                                            ]),

                                        TagsInput::make('certifications')
                                            ->label('Certifications')
                                            ->placeholder('Add certifications...')
                                            ->maxTags(10),

                                        FileUpload::make('resume')
                                            ->label('Resume / CV')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(5120),
                                    ]),
                            ]),

                        // Tab 3: Preferences & Settings
                        Tab::make('preferences_tab')
                            ->label('Preferences')
                            ->icon('settings')
                            ->schema([
                                Section::make('notifications_section')
                                    ->heading('Notification Settings')
                                    ->description('How would you like to be notified?')
//                                    ->collapsible()
//                                    ->collapsed()
                                    ->schema([
                                        Grid::make('notifications_grid')
                                            ->columns(2)
                                            ->schema([
                                                Toggle::make('email_notifications')
                                                    ->label('Email Notifications')
                                                    ->onLabel('Enabled')
                                                    ->offLabel('Disabled'),

                                                Toggle::make('sms_notifications')
                                                    ->label('SMS Notifications')
                                                    ->onLabel('Enabled')
                                                    ->offLabel('Disabled'),

                                                Toggle::make('push_notifications')
                                                    ->label('Push Notifications')
                                                    ->onLabel('Enabled')
                                                    ->offLabel('Disabled'),

                                                Toggle::make('newsletter')
                                                    ->label('Newsletter Subscription')
                                                    ->onLabel('Subscribed')
                                                    ->offLabel('Unsubscribed'),
                                            ]),

                                        Checkbox::make('notification_types')
                                            ->label('Notification Types')
                                            ->options([
                                                'updates' => 'Product Updates',
                                                'marketing' => 'Marketing Emails',
                                                'security' => 'Security Alerts',
                                                'social' => 'Social Activity',
                                            ])
                                            ->inline(),
                                    ]),

                                Section::make('appearance_section')
                                    ->heading('Appearance')
                                    ->description('Customize your interface')
//                                    ->collapsible()
//                                    ->collapsed()
                                    ->schema([
                                        Grid::make('appearance_grid')
                                            ->columns(2)
                                            ->schema([
                                                ColorPicker::make('theme_color')
                                                    ->label('Theme Color')
                                                    ->swatches([
                                                        '#ef4444',
                                                        '#f59e0b',
                                                        '#10b981',
                                                        '#3b82f6',
                                                        '#6366f1',
                                                    ]),

                                                IconPicker::make('favorite_icon')
                                                    ->label('Favorite Icon')
                                                    ->helperText('Choose your favorite icon')
                                                    ->searchable()
                                                    ->showIconName()
                                                    ->gridColumns(6),

                                                Radio::make('theme_mode')
                                                    ->label('Theme Mode')
                                                    ->options([
                                                        'light' => 'Light',
                                                        'dark' => 'Dark',
                                                        'auto' => 'Auto',
                                                    ])
                                                    ->inline(),

                                                Select::make('font_size')
                                                    ->label('Font Size')
                                                    ->options([
                                                        'small' => 'Small',
                                                        'medium' => 'Medium',
                                                        'large' => 'Large',
                                                    ]),

                                                Select::make('language_ui')
                                                    ->label('Interface Language')
                                                    ->options([
                                                        'en' => 'English',
                                                        'es' => 'Spanish',
                                                        'fr' => 'French',
                                                    ]),
                                            ]),
                                    ]),

                                Section::make('numbers_section')
                                    ->heading('Numbers & Measurements')
                                    ->description('Numeric input fields with different formats')
//                                    ->collapsible()
//                                    ->collapsed()
                                    ->schema([
                                        Grid::make('numbers_grid')
                                            ->columns(2)
                                            ->schema([
                                                NumberField::make('quantity')
                                                    ->label('Quantity')
                                                    ->helperText('Select a quantity')
                                                    ->min(0)
                                                    ->max(100)
                                                    ->step(1),

                                                NumberField::make('price')
                                                    ->label('Price')
                                                    ->currency('USD')
                                                    ->helperText('Enter the price in USD')
                                                    ->min(0)
                                                    ->step(0.01),

                                                NumberField::make('discount')
                                                    ->label('Discount')
                                                    ->percentage()
                                                    ->helperText('Enter discount percentage')
                                                    ->min(0)
                                                    ->max(1),

                                                NumberField::make('weight')
                                                    ->label('Weight')
                                                    ->suffix('kg')
                                                    ->helperText('Enter weight in kilograms')
                                                    ->min(0)
                                                    ->step(0.1),

                                                NumberField::make('temperature')
                                                    ->label('Temperature')
                                                    ->suffix('°C')
                                                    ->helperText('Enter temperature in Celsius')
                                                    ->step(0.5),

                                                NumberField::make('score')
                                                    ->label('Score')
                                                    ->prefix('⭐')
                                                    ->helperText('Rate from 0 to 10')
                                                    ->min(0)
                                                    ->max(10)
                                                    ->step(0.5),
                                            ]),
                                    ]),

                                Section::make('inputs_section')
                                    ->heading('Special Inputs')
                                    ->description('Pin input and rating fields')
//                                    ->collapsible()
//                                    ->collapsed()
                                    ->schema([
                                        Grid::make('special_inputs_grid')
                                            ->columns(2)
                                            ->schema([
                                                PinInput::make('verification_code')
                                                    ->label('Verification Code')
                                                    ->helperText('Enter 6-digit code')
                                                    ->length(6)
                                                    ->otp(),

                                                PinInput::make('password_pin')
                                                    ->label('Password PIN')
                                                    ->helperText('Enter 4-digit PIN')
                                                    ->length(4)
                                                    ->mask()
                                                    ->type('numeric'),

                                                RateInput::make('product_rating')
                                                    ->label('Product Rating')
                                                    ->helperText('Rate this product')
                                                    ->maxRating(5)
                                                    ->showValue(),

                                                RateInput::make('service_rating')
                                                    ->label('Service Rating')
                                                    ->helperText('How was our service?')
                                                    ->maxRating(5)
                                                    ->allowHalf()
                                                    ->showValue(),

                                                RateInput::make('favorite_level')
                                                    ->label('Favorite Level')
                                                    ->helperText('How much do you like this?')
                                                    ->maxRating(5)
                                                    ->icon('heart')
                                                    ->color('#ef4444'),

                                                RateInput::make('skill_level')
                                                    ->label('Skill Level')
                                                    ->helperText('Rate your expertise')
                                                    ->maxRating(10)
                                                    ->icon('trophy')
                                                    ->color('#f59e0b')
                                                    ->showValue(),
                                            ]),
                                    ]),

                                Section::make('privacy_section')
                                    ->heading('Privacy & Security')
                                    ->description('Control your data and security settings')
//                                    ->collapsible()
//                                    ->collapsed()
                                    ->schema([
                                        Toggle::make('public_profile')
                                            ->label('Public Profile')
                                            ->helperText('Make your profile visible to everyone'),

                                        Toggle::make('show_online_status')
                                            ->label('Show Online Status')
                                            ->helperText('Let others see when you\'re online'),

                                        Checkbox::make('privacy_options')
                                            ->label('Privacy Options')
                                            ->options([
                                                'search' => 'Allow search engines to index profile',
                                                'messages' => 'Allow messages from anyone',
                                                'mentions' => 'Allow mentions',
                                            ]),
                                    ]),
                            ]),

                        // Tab 4: Advanced Content
                        Tab::make('content_tab')
                            ->label('Content')
                            ->icon('file-text')
                            ->schema([
                                Section::make('content_section')
                                    ->heading('Content Creation')
                                    ->description('Create and manage your content')
//                                    ->collapsible()
//                                    ->collapsed()
                                    ->schema([
                                        RichEditor::make('content')
                                            ->label('Article Content')
                                            ->toolbarButtons(['bold', 'italic', 'heading', 'link', 'list'])
                                            ->showCharacterCount()
                                            ->helperText('Write your article content'),

                                        MarkdownEditor::make('markdown_content')
                                            ->label('Markdown Content')
                                            ->showCharacterCount()
                                            ->showWordCount()
                                            ->helperText('Write in markdown format'),

                                        Grid::make('content_grid')
                                            ->columns(2)
                                            ->schema([
                                                TagsInput::make('tags')
                                                    ->label('Content Tags')
                                                    ->placeholder('Add tags...')
                                                    ->maxTags(10),

                                                Select::make('category')
                                                    ->label('Category')
                                                    ->options([
                                                        'tech' => 'Technology',
                                                        'design' => 'Design',
                                                        'business' => 'Business',
                                                    ]),
                                            ]),

                                        FileUpload::make('featured_image')
                                            ->label('Featured Image')
                                            ->image()
                                            ->maxSize(5120),

                                        KeyValue::make('metadata')
                                            ->label('Custom Metadata')
                                            ->keyLabel('Property')
                                            ->valueLabel('Value')
                                            ->reorderable(),
                                    ]),
                            ]),

                        // Tab 5: Builder & Repeater Testing
                        Tab::make('builder_repeater_tab')
                            ->label('Builder & Repeater')
                            ->icon('layers')
                            ->schema([
                                Section::make('repeater_section')
                                    ->heading('Repeater Examples')
                                    ->description('Test various repeater configurations')
                                    ->schema([
                                        // Simple repeater with basic fields
                                        Repeater::make('team_members')
                                            ->label('Team Members')
                                            ->helperText('Add team members with their details')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Full Name')
                                                    ->required()
                                                    ->placeholder('John Doe'),

                                                TextInput::make('email')
                                                    ->label('Email')
                                                    ->email()
                                                    ->required()
                                                    ->placeholder('john@example.com'),

                                                TextInput::make('role')
                                                    ->label('Role')
                                                    ->required()
                                                    ->placeholder('Developer'),

                                                Select::make('department')
                                                    ->label('Department')
                                                    ->options([
                                                        'engineering' => 'Engineering',
                                                        'design' => 'Design',
                                                        'marketing' => 'Marketing',
                                                        'sales' => 'Sales',
                                                    ])
                                                    ->required(),
                                            ])
                                            ->minItems(1)
                                            ->maxItems(10)
                                            ->deletable()
                                            ->reorderable()
                                            ->collapsible()
                                            ->cloneable()
                                            ->addButtonLabel('Add Team Member'),

                                        // Complex repeater with all field types
                                        Repeater::make('projects')
                                            ->label('Projects Portfolio')
                                            ->helperText('Manage your project portfolio')
                                            ->schema([
                                                TextInput::make('project_name')
                                                    ->label('Project Name')
                                                    ->required(),

                                                Textarea::make('description')
                                                    ->label('Description')
                                                    ->rows(3),

                                                DatePicker::make('start_date')
                                                    ->label('Start Date'),

                                                DatePicker::make('end_date')
                                                    ->label('End Date'),

                                                Select::make('status')
                                                    ->label('Status')
                                                    ->options([
                                                        'planning' => 'Planning',
                                                        'active' => 'Active',
                                                        'completed' => 'Completed',
                                                        'on-hold' => 'On Hold',
                                                    ]),

                                                TagsInput::make('technologies')
                                                    ->label('Technologies Used'),

                                                ColorPicker::make('brand_color')
                                                    ->label('Brand Color'),

                                                Toggle::make('featured')
                                                    ->label('Featured Project'),
                                            ])
                                            ->minItems(0)
                                            ->maxItems(5)
                                            ->deletable()
                                            ->reorderable()
                                            ->collapsible()
                                            ->cloneable()
                                            ->addButtonLabel('Add Project'),

                                        // Nested repeater example
                                        Repeater::make('education')
                                            ->label('Education History')
                                            ->helperText('Add your educational background')
                                            ->schema([
                                                TextInput::make('institution')
                                                    ->label('Institution Name')
                                                    ->required(),

                                                TextInput::make('degree')
                                                    ->label('Degree')
                                                    ->required(),

                                                TextInput::make('field')
                                                    ->label('Field of Study'),

                                                DatePicker::make('graduation_date')
                                                    ->label('Graduation Date'),

                                                Repeater::make('achievements')
                                                    ->label('Achievements')
                                                    ->schema([
                                                        TextInput::make('achievement')
                                                            ->label('Achievement')
                                                            ->placeholder('Dean\'s List, Honor Roll, etc.'),
                                                    ])
                                                    ->minItems(0)
                                                    ->maxItems(5)
                                                    ->deletable()
                                                    ->addButtonLabel('Add Achievement'),
                                            ])
                                            ->minItems(0)
                                            ->deletable()
                                            ->reorderable()
                                            ->collapsible()
                                            ->addButtonLabel('Add Education'),
                                    ]),

                                Section::make('builder_section')
                                    ->heading('Builder Examples')
                                    ->description('Test page builder functionality')
                                    ->schema([
                                        // Simple builder with different block types
                                        Builder::make('page_content')
                                            ->label('Page Content')
                                            ->helperText('Build your page content using various blocks')
                                            ->blocks([
                                                Block::make('heading')
                                                    ->label('Heading')
                                                    ->icon('heading')
                                                    ->schema([
                                                        TextInput::make('content')
                                                            ->label('Heading Text')
                                                            ->required(),

                                                        Select::make('level')
                                                            ->label('Heading Level')
                                                            ->options([
                                                                'h1' => 'H1',
                                                                'h2' => 'H2',
                                                                'h3' => 'H3',
                                                                'h4' => 'H4',
                                                            ])
                                                            ->required(),
                                                    ]),

                                                Block::make('paragraph')
                                                    ->label('Paragraph')
                                                    ->icon('text')
                                                    ->schema([
                                                        Textarea::make('content')
                                                            ->label('Paragraph Text')
                                                            ->rows(4)
                                                            ->required(),
                                                    ]),

                                                Block::make('image')
                                                    ->label('Image')
                                                    ->icon('image')
                                                    ->schema([
                                                        FileUpload::make('image')
                                                            ->label('Image File')
                                                            ->image()
                                                            ->required(),

                                                        TextInput::make('alt')
                                                            ->label('Alt Text'),

                                                        TextInput::make('caption')
                                                            ->label('Caption'),
                                                    ]),

                                                Block::make('quote')
                                                    ->label('Quote')
                                                    ->icon('quote')
                                                    ->schema([
                                                        Textarea::make('quote')
                                                            ->label('Quote Text')
                                                            ->rows(3)
                                                            ->required(),

                                                        TextInput::make('author')
                                                            ->label('Author')
                                                            ->required(),

                                                        TextInput::make('source')
                                                            ->label('Source'),
                                                    ]),

                                                Block::make('code')
                                                    ->label('Code Block')
                                                    ->icon('code')
                                                    ->schema([
                                                        CodeEditor::make('code')
                                                            ->label('Code')
                                                            ->required(),

                                                        Select::make('language')
                                                            ->label('Language')
                                                            ->options([
                                                                'php' => 'PHP',
                                                                'javascript' => 'JavaScript',
                                                                'python' => 'Python',
                                                                'html' => 'HTML',
                                                                'css' => 'CSS',
                                                            ]),
                                                    ]),

                                                Block::make('list')
                                                    ->label('List')
                                                    ->icon('list')
                                                    ->schema([
                                                        Radio::make('type')
                                                            ->label('List Type')
                                                            ->options([
                                                                'unordered' => 'Bullet Points',
                                                                'ordered' => 'Numbered',
                                                            ])
                                                            ->required()
                                                            ->inline(),

                                                        Repeater::make('items')
                                                            ->label('List Items')
                                                            ->schema([
                                                                TextInput::make('item')
                                                                    ->label('Item')
                                                                    ->required(),
                                                            ])
                                                            ->minItems(1)
                                                            ->deletable()
                                                            ->reorderable()
                                                            ->addButtonLabel('Add Item'),
                                                    ]),
                                            ])
                                            ->minItems(0)
                                            ->maxItems(20)
                                            ->deletable()
                                            ->reorderable()
                                            ->collapsible()
                                            ->cloneable()
                                            ->blockNumbers()
                                            ->addActionLabel('Add Content Block'),

                                        // Advanced builder example
                                        Builder::make('landing_sections')
                                            ->label('Landing Page Sections')
                                            ->helperText('Build sections for your landing page')
                                            ->blocks([
                                                Block::make('hero')
                                                    ->label('Hero Section')
                                                    ->icon('layout-dashboard')
                                                    ->schema([
                                                        TextInput::make('headline')
                                                            ->label('Headline')
                                                            ->required(),

                                                        TextInput::make('subheadline')
                                                            ->label('Subheadline'),

                                                        Textarea::make('description')
                                                            ->label('Description')
                                                            ->rows(3),

                                                        FileUpload::make('background_image')
                                                            ->label('Background Image')
                                                            ->image(),

                                                        TextInput::make('cta_text')
                                                            ->label('CTA Button Text')
                                                            ->placeholder('Get Started'),

                                                        TextInput::make('cta_link')
                                                            ->label('CTA Button Link')
                                                            ->url(),
                                                    ]),

                                                Block::make('features')
                                                    ->label('Features Section')
                                                    ->icon('sparkles')
                                                    ->schema([
                                                        TextInput::make('section_title')
                                                            ->label('Section Title')
                                                            ->required(),

                                                        Repeater::make('features')
                                                            ->label('Features')
                                                            ->schema([
                                                                TextInput::make('icon')
                                                                    ->label('Icon Name')
                                                                    ->placeholder('check-circle'),

                                                                TextInput::make('title')
                                                                    ->label('Feature Title')
                                                                    ->required(),

                                                                Textarea::make('description')
                                                                    ->label('Description')
                                                                    ->rows(2),
                                                            ])
                                                            ->minItems(1)
                                                            ->maxItems(6)
                                                            ->deletable()
                                                            ->reorderable()
                                                            ->collapsible()
                                                            ->addButtonLabel('Add Feature'),
                                                    ]),

                                                Block::make('testimonials')
                                                    ->label('Testimonials Section')
                                                    ->icon('message-square')
                                                    ->schema([
                                                        TextInput::make('section_title')
                                                            ->label('Section Title')
                                                            ->required(),

                                                        Repeater::make('testimonials')
                                                            ->label('Testimonials')
                                                            ->schema([
                                                                Textarea::make('quote')
                                                                    ->label('Quote')
                                                                    ->rows(3)
                                                                    ->required(),

                                                                TextInput::make('author')
                                                                    ->label('Author Name')
                                                                    ->required(),

                                                                TextInput::make('position')
                                                                    ->label('Position/Company'),

                                                                FileUpload::make('avatar')
                                                                    ->label('Avatar')
                                                                    ->avatar(),
                                                            ])
                                                            ->minItems(1)
                                                            ->maxItems(10)
                                                            ->deletable()
                                                            ->reorderable()
                                                            ->collapsible()
                                                            ->addButtonLabel('Add Testimonial'),
                                                    ]),

                                                Block::make('cta')
                                                    ->label('Call to Action')
                                                    ->icon('megaphone')
                                                    ->schema([
                                                        TextInput::make('title')
                                                            ->label('Title')
                                                            ->required(),

                                                        Textarea::make('description')
                                                            ->label('Description')
                                                            ->rows(2),

                                                        TextInput::make('button_text')
                                                            ->label('Button Text')
                                                            ->required(),

                                                        TextInput::make('button_link')
                                                            ->label('Button Link')
                                                            ->url()
                                                            ->required(),

                                                        ColorPicker::make('background_color')
                                                            ->label('Background Color'),
                                                    ]),
                                            ])
                                            ->minItems(0)
                                            ->deletable()
                                            ->reorderable()
                                            ->collapsible()
                                            ->cloneable()
                                            ->blockNumbers()
                                            ->addActionLabel('Add Section'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
