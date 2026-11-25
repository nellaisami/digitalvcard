<?php

namespace App\Support;

class PlanFeatureCatalog
{
    public static function features(): array
    {
        return [
            'services' => 'Services',
            'products' => 'Products',
            'gallery' => 'Gallery',
            'testimonials' => 'Testimonials',
            'blogs' => 'Blogs',
            'social_links_website' => 'Social Links — Website',
            'custom_links' => 'Custom Links',
            'analytics' => 'Analytics',
            'seo' => 'SEO',
            'inquiry_form' => 'Inquiry Form',
            'appointments' => 'Appointments',
            'custom_qr_code' => 'Custom QR Code',
            'instagram_feed' => 'Instagram Feed',
            'linkedin_feed' => 'LinkedIn Feed',
            'iframes' => 'Iframes',
            'dynamic_vcard' => 'Dynamic vCard',
            'allow_custom_domain' => 'Allow Custom Domain',
            'whatsapp_stores' => 'WhatsApp Stores',
            'no_of_vcards' => 'Multi Templates',
            'no_of_visitors' => 'Number of Visitors',
            'hide_branding' => 'Hide Branding',
            'no_of_organization_users' => 'Number of Organization Users',
            'custom_css' => 'Custom CSS',
            'custom_js' => 'Custom JS',
            'custom_fonts' => 'Custom Fonts',
            'password_protection' => 'Password Protection',
        ];
    }

    public static function configurableLimits(): array
    {
        return [
            'no_of_visitors' => 'Number of Visitors',
            'no_of_organization_users' => 'Number of Organization Users',
        ];
    }
}
