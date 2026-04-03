<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function privacy()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Privacy Policy for ShareCart',
                'last_updated' => date('F j, Y'),
                'sections' => [
                    [
                        'heading' => '1. Information We Collect',
                        'content' => "Account Information: Name, email, and password.\nList Data: Grocery lists, items, and statuses.\nCollaborator Data: Associations with invited users.\nDevice Information: FCM tokens for push notifications."
                    ],
                    [
                        'heading' => '2. How We Use Your Information',
                        'content' => "To provide and improve ShareCart.\nTo sync grocery lists in real-time.\nTo send push notifications (e.g., when added to a list or nudged).\nTo calculate fair share settlements."
                    ],
                    [
                        'heading' => '3. Information Sharing and Disclosure',
                        'content' => "Your grocery lists are private. We do not sell your personal data. We only share information with users you invite."
                    ],
                    [
                        'heading' => '4. Data Security',
                        'content' => "We implement reasonable security measures, but no method is 100% secure over the internet."
                    ],
                    [
                        'heading' => '5. Your Rights',
                        'content' => "You can access, update, or delete your account and lists at any time."
                    ],
                    [
                        'heading' => '6. Contact Us',
                        'content' => "Email us at support@sharecart.com."
                    ],
                ]
            ]
        ]);
    }

    public function terms()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'title' => 'Terms and Conditions',
                'last_updated' => date('F j, Y'),
                'sections' => [
                    [
                        'heading' => '1. Agreement to Terms',
                        'content' => "By using ShareCart, you agree to these Terms."
                    ],
                    [
                        'heading' => '2. Use of the Service',
                        'content' => "You are responsible for safeguarding your password and using the app legally."
                    ],
                    [
                        'heading' => '3. User Content',
                        'content' => "You are responsible for your grocery list contents. Do not share offensive material."
                    ],
                    [
                        'heading' => '4. Termination',
                        'content' => "We may suspend your account for violating these terms."
                    ],
                    [
                        'heading' => '5. Limitation of Liability',
                        'content' => "ShareCart is not liable for indirect or incidental damages."
                    ]
                ]
            ]
        ]);
    }

    public function faq()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'faqs' => [
                    [
                        'question' => 'What is ShareCart?',
                        'answer' => 'A collaborative grocery list application to easily manage shopping with others.'
                    ],
                    [
                        'question' => 'How do I share a list?',
                        'answer' => 'Via Email Invitation, Invite Link, or a 5-digit Join Code.'
                    ],
                    [
                        'question' => 'What does "Claim Item" mean?',
                        'answer' => 'It marks an item so others know you are responsible for buying it.'
                    ],
                    [
                        'question' => 'What does the "Nudge!" button do?',
                        'answer' => 'It sends a push notification to all collaborators (e.g., to say you are at the store).'
                    ],
                    [
                        'question' => 'How does the Settlement feature work?',
                        'answer' => 'It tracks payments and calculates the "Fair Share" to easily settle up after shopping.'
                    ],
                    [
                        'question' => 'Do I need an account to use ShareCart?',
                        'answer' => 'You need an account to create lists, but you can join a shared list as a Guest using a Join Code.'
                    ]
                ]
            ]
        ]);
    }
}
