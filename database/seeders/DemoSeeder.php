<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Models\ProductVariant;
use App\Models\SalesChannel;
use App\Models\ShippingZone;
use App\Models\StockItem;
use App\Models\StoreLocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds a realistic demo catalog mirroring the nameeks.com Remer faucet
 * example: a Bathroom Faucets category with the full Overview / More
 * Features / Certifications / Specifications / Info & Guides schema, plus a
 * Vanity with Handle Finish x Vanity Finish x Size variants, a cross-category
 * Collection, a coupon, an offer, shipping, and store locations.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRolesAndAdmin();
        $channels = $this->seedSalesChannels();
        $this->seedShipping();
        $this->seedStoreLocations();

        $fixturesCategory = Category::firstOrCreate(
            ['slug' => 'bathroom-fixtures'],
            ['name' => 'Bathroom Fixtures', 'is_active' => true, 'sort_order' => 1]
        );

        $faucetsCategory = $this->seedBathroomFaucetsCategory($fixturesCategory);
        $vanitiesCategory = $this->seedVanitiesCategory($fixturesCategory);
        $mirrorsCategory = $this->seedMirrorsCategory($fixturesCategory);

        $remer = Brand::firstOrCreate(['slug' => 'remer'], ['name' => 'Remer', 'is_active' => true]);

        $faucet = $this->seedRemerFaucet($faucetsCategory, $remer, $channels);
        $vanityVariants = $this->seedVanity($vanitiesCategory, $channels);
        $mirror = $this->seedMirror($mirrorsCategory, $channels);

        $this->seedCollection($faucetsCategory, $vanitiesCategory, $mirrorsCategory, $faucet, $vanityVariants, $mirror);
        $this->seedCouponsAndOffers($faucetsCategory);
    }

    protected function seedRolesAndAdmin(): void
    {
        Permission::firstOrCreate(['name' => 'manage orders']);

        foreach (['super admin', 'catalog manager', 'order manager', 'support'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        Role::findByName('super admin')->givePermissionTo('manage orders');
        Role::findByName('order manager')->givePermissionTo('manage orders');

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );
        $admin->assignRole('super admin');
    }

    protected function seedSalesChannels(): array
    {
        return [
            'online' => SalesChannel::firstOrCreate(['code' => 'online'], ['name' => 'Online Store']),
            'wholesale' => SalesChannel::firstOrCreate(['code' => 'wholesale'], ['name' => 'Wholesale B2B']),
            'pos' => SalesChannel::firstOrCreate(['code' => 'pos'], ['name' => 'Point of Sale']),
        ];
    }

    protected function seedShipping(): void
    {
        $zone = ShippingZone::firstOrCreate(
            ['name' => 'United States'],
            ['countries' => ['United States'], 'is_active' => true]
        );

        $zone->rates()->firstOrCreate(
            ['name' => 'Standard Shipping'],
            [
                'calculation_type' => 'free_over_threshold',
                'base_rate' => 9.99,
                'free_over_amount' => 500,
                'estimated_days_min' => 3,
                'estimated_days_max' => 5,
                'is_active' => true,
            ]
        );

        $zone->rates()->firstOrCreate(
            ['name' => 'Express Shipping'],
            [
                'calculation_type' => 'flat',
                'base_rate' => 29.99,
                'estimated_days_min' => 1,
                'estimated_days_max' => 2,
                'is_active' => true,
            ]
        );
    }

    protected function seedStoreLocations(): void
    {
        StoreLocation::firstOrCreate(['name' => 'Downtown Showroom'], [
            'address_line_1' => '123 Market St',
            'city' => 'San Francisco',
            'state' => 'CA',
            'postal_code' => '94103',
            'country' => 'United States',
            'phone' => '(415) 555-0100',
            'is_active' => true,
        ]);

        StoreLocation::firstOrCreate(['name' => 'Uptown Design Center'], [
            'address_line_1' => '456 5th Ave',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10018',
            'country' => 'United States',
            'phone' => '(212) 555-0100',
            'is_active' => true,
        ]);
    }

    protected function seedBathroomFaucetsCategory(Category $parent): Category
    {
        $category = Category::firstOrCreate(
            ['slug' => 'bathroom-faucets'],
            [
                'parent_id' => $parent->id,
                'name' => 'Bathroom Faucets',
                'description' => 'Sink faucets in every finish, from modern matte black to classic chrome.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $overview = $category->attributeGroups()->firstOrCreate(['key' => 'overview'], ['label' => 'Overview', 'type' => 'richtext', 'sort_order' => 1]);
        $overview->fields()->firstOrCreate(['key' => 'overview_text'], ['label' => 'Overview', 'input_type' => 'textarea', 'sort_order' => 1]);
        $overview->fields()->firstOrCreate(['key' => 'overview_bullets'], ['label' => 'Highlights', 'input_type' => 'textarea', 'sort_order' => 2]);

        $moreFeatures = $category->attributeGroups()->firstOrCreate(['key' => 'more_features'], ['label' => 'More Features', 'type' => 'key_value', 'sort_order' => 2]);
        $finish = $moreFeatures->fields()->firstOrCreate(['key' => 'finish'], [
            'label' => 'Finish', 'input_type' => 'select', 'is_variant_option' => true, 'is_filterable' => true, 'sort_order' => 1,
            'options' => ['Matte Black', 'Brushed Nickel', 'Chrome', 'Oil Rubbed Bronze'],
        ]);
        $moreFeatures->fields()->firstOrCreate(['key' => 'type'], ['label' => 'Type', 'input_type' => 'text', 'sort_order' => 2]);
        $moreFeatures->fields()->firstOrCreate(['key' => 'watersense_certified'], ['label' => 'WaterSense Certified', 'input_type' => 'text', 'sort_order' => 3]);
        $moreFeatures->fields()->firstOrCreate(['key' => 'handle_style'], ['label' => 'Handle Style', 'input_type' => 'text', 'sort_order' => 4]);
        $moreFeatures->fields()->firstOrCreate(['key' => 'number_of_handles'], ['label' => 'Number of Handles', 'input_type' => 'text', 'sort_order' => 5]);
        $moreFeatures->fields()->firstOrCreate(['key' => 'flow_rate'], ['label' => 'Faucet Flow Rate', 'input_type' => 'text', 'is_filterable' => true, 'sort_order' => 6]);
        $moreFeatures->fields()->firstOrCreate(['key' => 'faucet_holes'], ['label' => 'Faucet Holes', 'input_type' => 'text', 'sort_order' => 7]);

        $certifications = $category->attributeGroups()->firstOrCreate(['key' => 'certifications'], ['label' => 'Certifications', 'type' => 'badge_list', 'sort_order' => 3]);
        $certifications->fields()->firstOrCreate(['key' => 'certification_badges'], ['label' => 'Certifications', 'input_type' => 'textarea', 'sort_order' => 1]);

        $guides = $category->attributeGroups()->firstOrCreate(['key' => 'guides'], ['label' => 'Info & Guides', 'type' => 'file_list', 'sort_order' => 4]);
        $guides->fields()->firstOrCreate(['key' => 'warranty_info'], ['label' => 'Warranty Info', 'input_type' => 'file', 'sort_order' => 1]);
        $guides->fields()->firstOrCreate(['key' => 'spec_sheet'], ['label' => 'Detailed Specification Sheet', 'input_type' => 'file', 'sort_order' => 2]);
        $guides->fields()->firstOrCreate(['key' => 'installation_guide'], ['label' => 'Faucet Installation Guide', 'input_type' => 'file', 'sort_order' => 3]);

        $specs = $category->attributeGroups()->firstOrCreate(['key' => 'specifications'], ['label' => 'Specifications', 'type' => 'key_value', 'sort_order' => 5]);
        $specs->fields()->firstOrCreate(['key' => 'spout_reach'], ['label' => 'Spout Reach', 'input_type' => 'text', 'sort_order' => 1]);
        $specs->fields()->firstOrCreate(['key' => 'spout_height'], ['label' => 'Spout Height', 'input_type' => 'text', 'sort_order' => 2]);
        $specs->fields()->firstOrCreate(['key' => 'overall_height'], ['label' => 'Overall Height', 'input_type' => 'text', 'sort_order' => 3]);
        $specs->fields()->firstOrCreate(['key' => 'weight'], ['label' => 'Weight', 'input_type' => 'text', 'sort_order' => 4]);

        return $category->fresh();
    }

    protected function seedVanitiesCategory(Category $parent): Category
    {
        $category = Category::firstOrCreate(
            ['slug' => 'vanities'],
            [
                'parent_id' => $parent->id,
                'name' => 'Bathroom Vanities',
                'description' => 'Vanities available in a range of handle finishes, cabinet finishes, and sizes.',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $moreFeatures = $category->attributeGroups()->firstOrCreate(['key' => 'more_features'], ['label' => 'More Features', 'type' => 'key_value', 'sort_order' => 1]);

        $moreFeatures->fields()->firstOrCreate(['key' => 'handle_finish'], [
            'label' => 'Handle Finish', 'input_type' => 'select', 'is_variant_option' => true, 'is_filterable' => true, 'sort_order' => 1,
            'options' => ['Chrome', 'Brushed Nickel', 'Matte Black'],
        ]);
        $moreFeatures->fields()->firstOrCreate(['key' => 'vanity_finish'], [
            'label' => 'Vanity Finish', 'input_type' => 'select', 'is_variant_option' => true, 'is_filterable' => true, 'sort_order' => 2,
            'options' => ['White', 'Grey Oak', 'Espresso'],
        ]);
        $moreFeatures->fields()->firstOrCreate(['key' => 'size'], [
            'label' => 'Size', 'input_type' => 'select', 'is_variant_option' => true, 'is_filterable' => true, 'sort_order' => 3,
            'options' => ['24"', '30"', '36"'],
        ]);
        $moreFeatures->fields()->firstOrCreate(['key' => 'countertop_material'], ['label' => 'Countertop Material', 'input_type' => 'text', 'sort_order' => 4]);
        $moreFeatures->fields()->firstOrCreate(['key' => 'number_of_sinks'], ['label' => 'Number of Sinks', 'input_type' => 'text', 'sort_order' => 5]);

        return $category->fresh();
    }

    protected function seedMirrorsCategory(Category $parent): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'bathroom-mirrors'],
            [
                'parent_id' => $parent->id,
                'name' => 'Bathroom Mirrors',
                'description' => 'LED and framed mirrors to complete the suite.',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );
    }

    protected function seedRemerFaucet(Category $category, Brand $brand, array $channels): Product
    {
        $product = Product::firstOrCreate(
            ['slug' => 'remer-l11usnl-no-matte-black-bathroom-faucet'],
            [
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => 'Matte Black Single Hole Bathroom Faucet',
                'base_sku' => 'L11USNL-NO',
                'msrp' => 471.00,
                'collection_line' => 'Class Line',
                'short_description' => 'The Remer Class Line matte black bathroom sink faucet is a perfect addition to your bathroom sink.',
                'is_active' => true,
                'is_featured' => true,
            ]
        );

        $groups = $category->attributeGroups()->with('fields')->get()->keyBy('key');

        $this->setAttr($product, $groups, 'overview', 'overview_text',
            'The Remer Class Line matte black bathroom sink faucet is a perfect addition to your bathroom sink. '
            .'Constructed out of high-quality brass in a matte black finish, this single hole bathroom faucet features '
            .'a modern sleek lever handle and has a spout reach of 4.3 inches. This faucet is CSA approved and certified '
            .'to NSF/ANSI 372 "lead free" plumbing products as defined by California, Vermont, Maryland and Louisiana '
            .'state law and by section 1417 of US Safe Drinking Water Act.'
        );

        $this->setAttr($product, $groups, 'overview', 'overview_bullets', implode("\n", [
            'Round bathroom faucet', 'Single hole installation', 'Made from high quality brass',
            'Matte black finish', 'Perfect for modern bathrooms', 'Ceramic disc technology cartridge',
            '1.2 GPM flow rate', 'Meets standards set by ADA', 'Made in Italy by Remer',
            'Part of the Class Line collection',
        ]));

        $this->setAttr($product, $groups, 'more_features', 'finish', 'Matte Black');
        $this->setAttr($product, $groups, 'more_features', 'type', 'Sink Faucets');
        $this->setAttr($product, $groups, 'more_features', 'watersense_certified', 'No');
        $this->setAttr($product, $groups, 'more_features', 'handle_style', 'Lever');
        $this->setAttr($product, $groups, 'more_features', 'number_of_handles', '1 Handle');
        $this->setAttr($product, $groups, 'more_features', 'flow_rate', '1.2 GPM');
        $this->setAttr($product, $groups, 'more_features', 'faucet_holes', '1 Hole');

        $this->setAttr($product, $groups, 'certifications', 'certification_badges', implode("\n", [
            'CSA Certified', 'US Standards Approved', 'Massachusetts Plumbing Code Approved',
            'NSF/ANSI 372-2016 Standards', 'ASME A112.18.1-2018/CSA B125.1-18', 'NSF/ANSI/CAN 61 Q < 1',
        ]));

        $this->setAttr($product, $groups, 'specifications', 'spout_reach', '4.33 Inches');
        $this->setAttr($product, $groups, 'specifications', 'spout_height', '3.66 Inches');
        $this->setAttr($product, $groups, 'specifications', 'overall_height', '5.91 Inches');
        $this->setAttr($product, $groups, 'specifications', 'weight', '2.2 Lbs');

        $variant = $product->variants()->firstOrCreate(
            ['sku' => 'L11USNL-NO'],
            ['price' => 471.00, 'is_default' => true, 'is_active' => true]
        );

        $finishAttr = $groups['more_features']->fields->firstWhere('key', 'finish');
        $variant->optionValues()->updateOrCreate(['category_attribute_id' => $finishAttr->id], ['value' => 'Matte Black']);

        $this->stock($variant, $channels['online'], 42);
        $this->stock($variant, $channels['wholesale'], 120);

        return $product->fresh('variants');
    }

    /**
     * @return ProductVariant[] a few representative variants across sizes/finishes
     */
    protected function seedVanity(Category $category, array $channels): array
    {
        $product = Product::firstOrCreate(
            ['slug' => 'modena-bathroom-vanity'],
            [
                'category_id' => $category->id,
                'name' => 'Modena Bathroom Vanity',
                'base_sku' => 'MODENA-VAN',
                'msrp' => 1299.00,
                'collection_line' => 'Modena Collection',
                'short_description' => 'A modern vanity available in multiple handle finishes, cabinet finishes, and sizes — each combination priced and stocked independently.',
                'is_active' => true,
                'is_featured' => true,
            ]
        );

        $group = $category->attributeGroups()->where('key', 'more_features')->first();
        $handleFinish = $group->fields->firstWhere('key', 'handle_finish') ?? $group->fields()->where('key', 'handle_finish')->first();
        $vanityFinish = $group->fields->firstWhere('key', 'vanity_finish') ?? $group->fields()->where('key', 'vanity_finish')->first();
        $size = $group->fields->firstWhere('key', 'size') ?? $group->fields()->where('key', 'size')->first();

        $this->setAttr($product, collect(['more_features' => $group]), 'more_features', 'countertop_material', 'Quartz');
        $this->setAttr($product, collect(['more_features' => $group]), 'more_features', 'number_of_sinks', '1 Sink');

        $combinations = [
            ['handle' => 'Matte Black', 'finish' => 'Espresso', 'size' => '24"', 'price' => 999.00, 'suffix' => 'BLK-ESP-24'],
            ['handle' => 'Matte Black', 'finish' => 'Espresso', 'size' => '30"', 'price' => 1199.00, 'suffix' => 'BLK-ESP-30'],
            ['handle' => 'Brushed Nickel', 'finish' => 'Grey Oak', 'size' => '30"', 'price' => 1249.00, 'suffix' => 'BN-OAK-30'],
            ['handle' => 'Chrome', 'finish' => 'White', 'size' => '36"', 'price' => 1399.00, 'suffix' => 'CHR-WHT-36'],
        ];

        $variants = [];

        foreach ($combinations as $combo) {
            $variant = $product->variants()->firstOrCreate(
                ['sku' => "MODENA-VAN-{$combo['suffix']}"],
                ['price' => $combo['price'], 'is_default' => $combo === $combinations[0], 'is_active' => true]
            );

            $variant->optionValues()->updateOrCreate(['category_attribute_id' => $handleFinish->id], ['value' => $combo['handle']]);
            $variant->optionValues()->updateOrCreate(['category_attribute_id' => $vanityFinish->id], ['value' => $combo['finish']]);
            $variant->optionValues()->updateOrCreate(['category_attribute_id' => $size->id], ['value' => $combo['size']]);

            $this->stock($variant, $channels['online'], random_int(3, 25));

            $variants[] = $variant;
        }

        return $variants;
    }

    protected function seedMirror(Category $category, array $channels): Product
    {
        $product = Product::firstOrCreate(
            ['slug' => 'modena-led-bathroom-mirror'],
            [
                'category_id' => $category->id,
                'name' => 'Modena LED Bathroom Mirror',
                'base_sku' => 'MODENA-MIR',
                'msrp' => 349.00,
                'collection_line' => 'Modena Collection',
                'short_description' => 'Anti-fog LED mirror with touch dimmer, matched to the Modena vanity line.',
                'is_active' => true,
            ]
        );

        $variant = $product->variants()->firstOrCreate(
            ['sku' => 'MODENA-MIR-30'],
            ['price' => 349.00, 'is_default' => true, 'is_active' => true]
        );

        $this->stock($variant, $channels['online'], 60);

        return $product->fresh('variants');
    }

    protected function seedCollection(
        Category $faucetsCategory,
        Category $vanitiesCategory,
        Category $mirrorsCategory,
        Product $faucet,
        array $vanityVariants,
        Product $mirror
    ): void {
        $collection = ProductCollection::firstOrCreate(
            ['slug' => 'modena-matte-black-bath-suite'],
            [
                'name' => 'Modena Matte Black Bath Suite',
                'description' => 'Buy the vanity, faucet, and mirror together as one suite and save 10% — mix and match finishes across categories.',
                'pricing_mode' => 'sum_of_selections',
                'discount_percent' => 10,
                'is_active' => true,
            ]
        );

        $collection->slots()->firstOrCreate(
            ['category_id' => $vanitiesCategory->id],
            ['label' => 'Choose your Vanity', 'is_required' => true, 'default_product_variant_id' => $vanityVariants[0]->id, 'sort_order' => 1]
        );

        $collection->slots()->firstOrCreate(
            ['category_id' => $faucetsCategory->id],
            ['label' => 'Choose your Faucet', 'is_required' => true, 'default_product_variant_id' => $faucet->defaultVariant()->id, 'sort_order' => 2]
        );

        $collection->slots()->firstOrCreate(
            ['category_id' => $mirrorsCategory->id],
            ['label' => 'Choose your Mirror', 'is_required' => false, 'default_product_variant_id' => $mirror->defaultVariant()->id, 'sort_order' => 3]
        );
    }

    protected function seedCouponsAndOffers(Category $faucetsCategory): void
    {
        Coupon::firstOrCreate(['code' => 'WELCOME10'], [
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => 50,
            'scope' => 'all',
            'is_active' => true,
        ]);

        Offer::firstOrCreate(['name' => 'Faucet Flash Sale'], [
            'type' => 'percentage_off',
            'value' => 15,
            'target_type' => 'category',
            'target_id' => $faucetsCategory->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'badge_label' => '15% OFF',
            'priority' => 10,
            'is_active' => true,
        ]);
    }

    protected function setAttr(Product $product, $groups, string $groupKey, string $attrKey, string $value): void
    {
        $group = $groups[$groupKey] ?? null;

        if (! $group) {
            return;
        }

        $attribute = $group->fields->firstWhere('key', $attrKey) ?? $group->fields()->where('key', $attrKey)->first();

        if (! $attribute) {
            return;
        }

        $product->attributeValues()->updateOrCreate(['category_attribute_id' => $attribute->id], ['value' => $value]);
    }

    protected function stock(ProductVariant $variant, SalesChannel $channel, int $quantity): void
    {
        StockItem::updateOrCreate(
            ['product_variant_id' => $variant->id, 'sales_channel_id' => $channel->id],
            ['quantity_on_hand' => $quantity, 'low_stock_threshold' => 5]
        );
    }
}
