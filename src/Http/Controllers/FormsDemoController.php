<?php

namespace Laravilt\Forms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravilt\Forms\Components\Checkbox;
use Laravilt\Forms\Components\ColorPicker;
use Laravilt\Forms\Components\DatePicker;
use Laravilt\Forms\Components\FileUpload;
use Laravilt\Forms\Components\KeyValue;
use Laravilt\Forms\Components\MarkdownEditor;
use Laravilt\Forms\Components\Radio;
use Laravilt\Forms\Components\RichEditor;
use Laravilt\Forms\Components\Select;
use Laravilt\Forms\Components\TagsInput;
use Laravilt\Forms\Components\Textarea;
use Laravilt\Forms\Components\TextInput;
use Laravilt\Forms\Components\Toggle;
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
                                                    ->label('Phone Number')
                                                    ->placeholder('+1 (555) 000-0000')
                                                    ->prefix('+1'),

                                                DatePicker::make('birthdate')
                                                    ->label('Date of Birth')
                                                    ->required(),
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
                    ]),
            ]);
    }
}
