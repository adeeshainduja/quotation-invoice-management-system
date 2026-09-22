<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyTemplate;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Inserts demo companies, templates, customers, quotations (with items),
     * invoices (with items), and payments without touching any existing records.
     */
    public function run(): void
    {
        // Use the first admin user as the creator for all demo records.
        $admin = User::where('role', 'ADMIN')->first();

        if (! $admin) {
            $this->command->error('No ADMIN user found. Please create an admin account first.');

            return;
        }

        $this->command->info('Seeding demo companies …');
        $companies = $this->seedCompanies();

        $this->command->info('Seeding company templates …');
        $this->seedTemplates($companies);

        $this->command->info('Seeding customers …');
        $this->seedCustomers($companies);

        $this->command->info('Seeding quotations …');
        $this->seedQuotations($admin);

        $this->command->info('Seeding invoices & payments …');
        $this->seedInvoicesAndPayments($admin);

        $this->command->info('Demo data seeded successfully.');
    }

    // -------------------------------------------------------------------------
    // Companies
    // -------------------------------------------------------------------------

    /**
     * @return Collection<int, Company>
     */
    private function seedCompanies(): Collection
    {
        $data = [
            [
                'name' => 'QMINIMORE Pty Ltd',
                'registration_number' => 'LK-BRN-10012',
                'address_line_1' => '45 Lotus Tower Road',
                'address_line_2' => '3rd Floor',
                'city' => 'Colombo',
                'country' => 'Sri Lanka',
                'phone' => '+94112345678',
                'email' => 'info@qminimore.lk',
                'website' => 'https://www.qminimore.lk',
                'logo_path' => 'logos/qminimore.png',
                'vat_registered' => true,
                'vat_number' => 'VAT-QMM-001',
                'vat_percentage' => 18.00,
                'quotation_prefix' => 'QMM-Q-',
                'quotation_next_number' => 1,
                'invoice_prefix' => 'QMM-INV-',
                'invoice_next_number' => 1,
                'currency' => 'LKR',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'ABC Solutions Pvt Ltd',
                'registration_number' => 'LK-BRN-20034',
                'address_line_1' => '112 Galle Road',
                'address_line_2' => null,
                'city' => 'Colombo',
                'country' => 'Sri Lanka',
                'phone' => '+94115556789',
                'email' => 'contact@abcsolutions.lk',
                'website' => 'https://www.abcsolutions.lk',
                'logo_path' => 'logos/abcsolutions.png',
                'vat_registered' => true,
                'vat_number' => 'VAT-ABC-002',
                'vat_percentage' => 18.00,
                'quotation_prefix' => 'ABC-Q-',
                'quotation_next_number' => 1,
                'invoice_prefix' => 'ABC-INV-',
                'invoice_next_number' => 1,
                'currency' => 'LKR',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Tech Lanka Pvt Ltd',
                'registration_number' => 'LK-BRN-30056',
                'address_line_1' => '78 Duplication Road',
                'address_line_2' => 'Suite 201',
                'city' => 'Colombo',
                'country' => 'Sri Lanka',
                'phone' => '+94117778888',
                'email' => 'hello@techlanka.lk',
                'website' => 'https://www.techlanka.lk',
                'logo_path' => 'logos/techlanka.png',
                'vat_registered' => false,
                'vat_number' => null,
                'vat_percentage' => null,
                'quotation_prefix' => 'TL-Q-',
                'quotation_next_number' => 1,
                'invoice_prefix' => 'TL-INV-',
                'invoice_next_number' => 1,
                'currency' => 'LKR',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Global Retail Group',
                'registration_number' => 'LK-BRN-40078',
                'address_line_1' => '22 Marine Drive',
                'address_line_2' => null,
                'city' => 'Colombo',
                'country' => 'Sri Lanka',
                'phone' => '+94114441234',
                'email' => 'ops@globalretail.lk',
                'website' => 'https://www.globalretail.lk',
                'logo_path' => 'logos/globalretail.png',
                'vat_registered' => true,
                'vat_number' => 'VAT-GRG-004',
                'vat_percentage' => 15.00,
                'quotation_prefix' => 'GRG-Q-',
                'quotation_next_number' => 1,
                'invoice_prefix' => 'GRG-INV-',
                'invoice_next_number' => 1,
                'currency' => 'USD',
                'status' => 'ACTIVE',
            ],
            [
                'name' => 'Future AI Systems',
                'registration_number' => 'LK-BRN-50099',
                'address_line_1' => '9 Parliament Road',
                'address_line_2' => 'Level 5',
                'city' => 'Sri Jayawardenepura',
                'country' => 'Sri Lanka',
                'phone' => '+94113339999',
                'email' => 'team@futureai.lk',
                'website' => 'https://www.futureai.lk',
                'logo_path' => 'logos/futureai.png',
                'vat_registered' => true,
                'vat_number' => 'VAT-FAI-005',
                'vat_percentage' => 18.00,
                'quotation_prefix' => 'FAI-Q-',
                'quotation_next_number' => 1,
                'invoice_prefix' => 'FAI-INV-',
                'invoice_next_number' => 1,
                'currency' => 'USD',
                'status' => 'ACTIVE',
            ],
        ];

        $companies = collect();
        foreach ($data as $row) {
            $companies->push(Company::create($row));
        }

        return $companies;
    }

    // -------------------------------------------------------------------------
    // Templates
    // -------------------------------------------------------------------------

    /**
     * @param  Collection<int, Company>  $companies
     */
    private function seedTemplates(Collection $companies): void
    {
        $termsText = "Payment is due within 30 days of invoice date.\nLate payments may attract a 1.5% monthly surcharge.\nAll disputes must be raised within 7 days of receipt.";

        foreach ($companies as $company) {
            CompanyTemplate::create([
                'company_id' => $company->id,
                'document_type' => 'QUOTATION',
                'template_name' => 'Standard Quotation',
                'header_text' => "Quotation from {$company->name}",
                'footer_text' => 'Thank you for considering our services.',
                'terms_conditions' => 'This quotation is valid for 30 days from the date of issue.',
                'show_logo' => true,
                'show_bank_details' => false,
                'show_vat' => $company->vat_registered,
                'show_signature' => true,
                'is_default' => true,
            ]);

            CompanyTemplate::create([
                'company_id' => $company->id,
                'document_type' => 'INVOICE',
                'template_name' => 'Standard Invoice',
                'header_text' => "Invoice from {$company->name}",
                'footer_text' => 'Thank you for your business.',
                'terms_conditions' => $termsText,
                'show_logo' => true,
                'show_bank_details' => true,
                'show_vat' => $company->vat_registered,
                'show_signature' => true,
                'is_default' => true,
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Customers
    // -------------------------------------------------------------------------

    /**
     * @param  Collection<int, Company>  $companies
     */
    private function seedCustomers(Collection $companies): void
    {
        // 10 customers per company = 50 total
        $customerBlocks = [
            // ---- QMINIMORE ----
            [
                ['customer_name' => 'Rohan Perera',        'business_name' => 'Perera Supermart',          'email' => 'rohan@perasupermart.lk',     'phone' => '+94770001001', 'address_line_1' => '14 Baseline Road',         'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Nimal Fernando',      'business_name' => 'Fernando Hardware Stores',   'email' => 'nimal@fernandohardware.lk',  'phone' => '+94770001002', 'address_line_1' => '55 Kandy Road',            'city' => 'Gampaha',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Kasun Silva',         'business_name' => 'Silva Constructions',        'email' => 'kasun@silvaconstruct.lk',    'phone' => '+94770001003', 'address_line_1' => '8 New Kandy Road',         'city' => 'Kadawatha',  'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Dilani Wickrama',     'business_name' => 'DW Catering Services',       'email' => 'dilani@dwcatering.lk',       'phone' => '+94770001004', 'address_line_1' => '22 Hospital Road',         'city' => 'Negombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Chaminda Jayawardena', 'business_name' => 'Jayawardena Auto Parts',     'email' => 'chaminda@jautoparts.lk',     'phone' => '+94770001005', 'address_line_1' => '31 Battaramulla Road',     'city' => 'Battaramulla', 'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Priya Seneviratne',   'business_name' => 'Priya Fashion Boutique',     'email' => 'priya@priyafashion.lk',      'phone' => '+94770001006', 'address_line_1' => '100 High Level Road',      'city' => 'Nugegoda',   'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Suresh Kumara',       'business_name' => 'Kumara Office Supplies',     'email' => 'suresh@kumaraoffice.lk',     'phone' => '+94770001007', 'address_line_1' => '7 Nawala Road',            'city' => 'Rajagiriya', 'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Amara Bandara',       'business_name' => 'Bandara Agro Exports',       'email' => 'amara@bandaraagro.lk',       'phone' => '+94770001008', 'address_line_1' => '3 Temple Road',            'city' => 'Kurunegala', 'country' => 'Sri Lanka', 'status' => 'INACTIVE'],
                ['customer_name' => 'Sampath Rathnayake',  'business_name' => 'SR Printing Solutions',      'email' => 'sampath@srprint.lk',         'phone' => '+94770001009', 'address_line_1' => '19 Union Place',           'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Thilini De Silva',    'business_name' => 'Thilini Beauty Parlour',     'email' => 'thilini@thilinibeauty.lk',   'phone' => '+94770001010', 'address_line_1' => '66 Havelock Town Road',    'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
            ],
            // ---- ABC Solutions ----
            [
                ['customer_name' => 'Marcus Jensen',       'business_name' => 'Jensen IT Consulting',       'email' => 'marcus@jensenit.lk',         'phone' => '+94770002001', 'address_line_1' => '5 Lotus Road',             'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Fathima Noor',        'business_name' => 'Noor Textiles Ltd',          'email' => 'fathima@noortextiles.lk',    'phone' => '+94770002002', 'address_line_1' => '88 Maradana Road',         'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Arun Krishnaswamy',   'business_name' => 'Krishna Restaurant Group',   'email' => 'arun@krishnarestaurant.lk',  'phone' => '+94770002003', 'address_line_1' => '12 Galle Face Court',      'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Banu Ratnasiri',      'business_name' => 'Ratnasiri Gems & Jewellery', 'email' => 'banu@ratnasirigems.lk',      'phone' => '+94770002004', 'address_line_1' => '77 Sea Street',            'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Victor Mendis',       'business_name' => 'Mendis Logistics Pvt Ltd',   'email' => 'victor@mendislogistics.lk',  'phone' => '+94770002005', 'address_line_1' => '34 Bloemendhal Road',      'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Sandya Wijesinghe',   'business_name' => 'SW Web Designs',             'email' => 'sandya@swwebdesign.lk',      'phone' => '+94770002006', 'address_line_1' => '45 Independence Ave',      'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Gayan Herath',        'business_name' => 'Herath Engineering Works',   'email' => 'gayan@heratheng.lk',         'phone' => '+94770002007', 'address_line_1' => '9 Kirulapone Avenue',      'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Roshani Gunewardena', 'business_name' => 'Roshani Pharma Distributors', 'email' => 'roshani@rpharma.lk',         'phone' => '+94770002008', 'address_line_1' => '62 Thurstan Road',         'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Janith Nanayakkara',  'business_name' => 'Janith Solar Power',         'email' => 'janith@janithsolar.lk',      'phone' => '+94770002009', 'address_line_1' => '16 Flower Road',           'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'INACTIVE'],
                ['customer_name' => 'Lakmali Perera',      'business_name' => 'Lakmali School of Dance',    'email' => 'lakmali@lakmalischool.lk',   'phone' => '+94770002010', 'address_line_1' => '3 Reid Avenue',            'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
            ],
            // ---- Tech Lanka ----
            [
                ['customer_name' => 'Harsha Aluthgama',    'business_name' => 'Aluthgama Digital Media',    'email' => 'harsha@aluthgamamedia.lk',   'phone' => '+94770003001', 'address_line_1' => '99 Duplication Road',      'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Imran Farook',        'business_name' => 'Farook Freight Services',    'email' => 'imran@farookfreight.lk',     'phone' => '+94770003002', 'address_line_1' => '5 Borupana Road',          'city' => 'Seeduwa',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Sachini Dissanayake', 'business_name' => 'Sachini Handloom Creations', 'email' => 'sachini@handloom.lk',        'phone' => '+94770003003', 'address_line_1' => '27 Kandy Road',            'city' => 'Kegalle',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Danushka Jayasinghe', 'business_name' => 'Jayasinghe Food Industries', 'email' => 'danushka@jfoodind.lk',       'phone' => '+94770003004', 'address_line_1' => '14 Main Street',           'city' => 'Matara',     'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Nirosha Senanayake',  'business_name' => 'Senanayake Event Management', 'email' => 'nirosha@sevents.lk',         'phone' => '+94770003005', 'address_line_1' => '6 Ananda Coomaraswamy Mw', 'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Udara Pathirana',     'business_name' => 'Pathirana Real Estate',      'email' => 'udara@pathiranareal.lk',     'phone' => '+94770003006', 'address_line_1' => '55 Cotta Road',            'city' => 'Boralesgamuwa', 'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Kavindra Ranasinghe', 'business_name' => 'Ranasinghe Poultry Farm',    'email' => 'kavindra@ranasinhefarm.lk',  'phone' => '+94770003007', 'address_line_1' => '3 Farm Road',              'city' => 'Veyangoda',  'country' => 'Sri Lanka', 'status' => 'INACTIVE'],
                ['customer_name' => 'Waruna Dissanayake',  'business_name' => 'Dissanayake Travel Agency',  'email' => 'waruna@dtravel.lk',          'phone' => '+94770003008', 'address_line_1' => '101 Galle Road',           'city' => 'Wellawatta', 'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Isuru Liyanage',      'business_name' => 'Liyanage Security Services', 'email' => 'isuru@liyanagesecurity.lk',  'phone' => '+94770003009', 'address_line_1' => '20 Baseline Road',         'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Malini Gunasekara',   'business_name' => 'Gunasekara Medical Centre',  'email' => 'malini@gunasekaramedical.lk', 'phone' => '+94770003010', 'address_line_1' => '14 De Saram Place',        'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
            ],
            // ---- Global Retail Group ----
            [
                ['customer_name' => 'David Weerasinghe',   'business_name' => 'Weerasinghe Superstore',     'email' => 'david@weeras.lk',            'phone' => '+94770004001', 'address_line_1' => '12 Horton Place',          'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Rashida Abdul',       'business_name' => 'Abdul Wholesale Trading',    'email' => 'rashida@abdulwholesale.lk',  'phone' => '+94770004002', 'address_line_1' => '44 Sea Street',            'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Jehan Siriwardena',   'business_name' => 'Siriwardena Dairy Products', 'email' => 'jehan@sdairy.lk',            'phone' => '+94770004003', 'address_line_1' => '7 Ambagamuwa Road',        'city' => 'Nuwara Eliya', 'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Manel Kiriella',      'business_name' => 'Kiriella Gem Exports',       'email' => 'manel@kiriellagems.lk',      'phone' => '+94770004004', 'address_line_1' => '3 Gem Street',             'city' => 'Ratnapura',  'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Shehan Abeysekera',   'business_name' => 'Abeysekera Law Associates',  'email' => 'shehan@abeslaw.lk',          'phone' => '+94770004005', 'address_line_1' => '29 Turret Road',           'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Thamara Wickremesinghe', 'business_name' => 'TW Home Decor',            'email' => 'thamara@twhomedecor.lk',     'phone' => '+94770004006', 'address_line_1' => '56 Barnes Place',          'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Lakshan Dias',        'business_name' => 'Dias Bakery Chain',          'email' => 'lakshan@diasbakery.lk',      'phone' => '+94770004007', 'address_line_1' => '8 Lauries Road',           'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Nuwani Rathnasiri',   'business_name' => 'NR Insurance Brokers',       'email' => 'nuwani@nrinsurance.lk',      'phone' => '+94770004008', 'address_line_1' => '22 Janadhipathi Mw',       'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'INACTIVE'],
                ['customer_name' => 'Roshan Mendis',       'business_name' => 'Mendis Fisheries Ltd',       'email' => 'roshan@mendisfisheries.lk',  'phone' => '+94770004009', 'address_line_1' => '1 Harbour Road',           'city' => 'Negombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Amali Senanayake',    'business_name' => 'Senanayake Leather Goods',   'email' => 'amali@senaleather.lk',       'phone' => '+94770004010', 'address_line_1' => '44 Manning Place',         'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
            ],
            // ---- Future AI Systems ----
            [
                ['customer_name' => 'Tharindu Gamage',     'business_name' => 'Gamage Cloud Services',      'email' => 'tharindu@gamagecloud.lk',    'phone' => '+94770005001', 'address_line_1' => '2 Baudhaloka Mw',          'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Sameera Pathak',      'business_name' => 'Pathak Data Analytics',      'email' => 'sameera@pathakdata.lk',      'phone' => '+94770005002', 'address_line_1' => '77 Independence Ave',      'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Vinitha Peris',       'business_name' => 'Peris Robotics Lab',         'email' => 'vinitha@perisrobotics.lk',   'phone' => '+94770005003', 'address_line_1' => '9 Rajagiriya Road',        'city' => 'Rajagiriya', 'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Nuwan Witharana',     'business_name' => 'Witharana E-Commerce Hub',   'email' => 'nuwan@witharanaec.lk',       'phone' => '+94770005004', 'address_line_1' => '18 Kirimandala Mw',        'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Dilrukshi Edirisinghe', 'business_name' => 'Edirisinghe Tech Academy',  'email' => 'dilrukshi@edirisingheta.lk', 'phone' => '+94770005005', 'address_line_1' => '30 Bauddhaloka Mw',        'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Malaka Samarawickrama', 'business_name' => 'Samarawickrama Drones Ltd', 'email' => 'malaka@swdrones.lk',         'phone' => '+94770005006', 'address_line_1' => '12 Parliament Road',       'city' => 'Kotte',      'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Danesh Pillai',       'business_name' => 'Pillai AI Research',         'email' => 'danesh@pillairesearch.lk',   'phone' => '+94770005007', 'address_line_1' => '3 Wijerama Mw',            'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Minoli Gunaratne',    'business_name' => 'Gunaratne Digital Branding', 'email' => 'minoli@gunaratnedigital.lk', 'phone' => '+94770005008', 'address_line_1' => '9 Horton Place',           'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
                ['customer_name' => 'Kasun Jayarathne',    'business_name' => 'Jayarathne Network Solns',   'email' => 'kasun@jayarathnenet.lk',     'phone' => '+94770005009', 'address_line_1' => '6 Isipathana Mw',          'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'INACTIVE'],
                ['customer_name' => 'Piyumi Jayakody',     'business_name' => 'Jayakody Startup Hub',       'email' => 'piyumi@jstartuphub.lk',     'phone' => '+94770005010', 'address_line_1' => '50 Duplication Road',      'city' => 'Colombo',    'country' => 'Sri Lanka', 'status' => 'ACTIVE'],
            ],
        ];

        foreach ($companies as $index => $company) {
            $block = $customerBlocks[$index] ?? [];
            foreach ($block as $row) {
                Customer::create(array_merge($row, ['company_id' => $company->id]));
            }
        }
    }

    // -------------------------------------------------------------------------
    // Quotations
    // -------------------------------------------------------------------------

    private function seedQuotations(User $admin): void
    {
        $companies = Company::with(['customers', 'templates'])->get();

        // Statuses distributed across 100 quotations
        // DRAFT:20, SENT:25, ACCEPTED:30, REJECTED:15, EXPIRED:10
        $statusPool = array_merge(
            array_fill(0, 20, 'DRAFT'),
            array_fill(0, 25, 'SENT'),
            array_fill(0, 30, 'ACCEPTED'),
            array_fill(0, 15, 'REJECTED'),
            array_fill(0, 10, 'EXPIRED')
        );
        shuffle($statusPool);

        $qIndex = 0;

        foreach ($companies as $company) {
            $customers = $company->customers;
            if ($customers->isEmpty()) {
                continue;
            }

            $qtTemplate = $company->templates->firstWhere('document_type', 'QUOTATION');
            if (! $qtTemplate) {
                continue;
            }

            // 20 quotations per company = 100 total
            for ($i = 1; $i <= 20; $i++) {
                $status = $statusPool[$qIndex++] ?? 'DRAFT';
                $quotationDate = Carbon::now()->subDays(rand(10, 360));
                $expiryDate = (clone $quotationDate)->addDays(30);

                $customer = $customers->random();
                $quotationNumber = $company->quotation_prefix.str_pad($i, 4, '0', STR_PAD_LEFT);

                [$items, $subtotal, $taxAmount, $grandTotal] = $this->generateLineItems();

                $companySnapshot = $this->companySnapshot($company);
                $customerSnapshot = $this->customerSnapshot($customer);
                $templateSnapshot = $this->templateSnapshot($qtTemplate);

                $quotation = Quotation::create([
                    'company_id' => $company->id,
                    'customer_id' => $customer->id,
                    'quotation_number' => $quotationNumber,
                    'quotation_date' => $quotationDate->toDateString(),
                    'expiry_date' => $expiryDate->toDateString(),
                    'subject' => $this->randomSubject(),
                    'project_name' => $this->randomProjectName(),
                    'subtotal' => $subtotal,
                    'discount_type' => 'NONE',
                    'discount_value' => 0,
                    'discount_amount' => 0,
                    'tax_percentage' => $company->vat_registered ? ($company->vat_percentage ?? 18) : 0,
                    'tax_amount' => $taxAmount,
                    'additional_charges' => 0,
                    'grand_total' => $grandTotal,
                    'status' => $status,
                    'notes' => 'All prices are inclusive of applicable taxes.',
                    'terms_conditions' => 'Valid for 30 days from issue date.',
                    'template_id' => $qtTemplate->id,
                    'company_snapshot' => $companySnapshot,
                    'customer_snapshot' => $customerSnapshot,
                    'template_snapshot' => $templateSnapshot,
                    'created_by' => $admin->id,
                ]);

                // Insert line items
                foreach ($items as $order => $item) {
                    QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'sort_order' => $order + 1,
                        'item_name' => $item['name'],
                        'description' => $item['description'],
                        'quantity' => $item['qty'],
                        'unit' => $item['unit'],
                        'unit_price' => $item['unit_price'],
                        'discount_type' => 'NONE',
                        'discount_value' => 0,
                        'discount_amount' => 0,
                        'tax_percentage' => 0,
                        'tax_amount' => 0,
                        'line_total' => $item['line_total'],
                    ]);
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Invoices & Payments
    // -------------------------------------------------------------------------

    private function seedInvoicesAndPayments(User $admin): void
    {
        // Create invoices from ACCEPTED quotations
        $acceptedQuotations = Quotation::with(['company.templates', 'customer'])
            ->where('status', 'ACCEPTED')
            ->get();

        foreach ($acceptedQuotations as $quotation) {
            $company = $quotation->company;
            $customer = $quotation->customer;

            $invTemplate = $company->templates->firstWhere('document_type', 'INVOICE');
            if (! $invTemplate) {
                continue;
            }

            // Determine invoice date (a few days after quotation date)
            $invoiceDate = Carbon::parse($quotation->quotation_date)->addDays(rand(1, 7));
            $dueDate = (clone $invoiceDate)->addDays(30);

            // Pick a status with weighted distribution
            $statusRoll = rand(1, 10);
            if ($statusRoll <= 4) {
                $invoiceStatus = 'PAID';
            } elseif ($statusRoll <= 7) {
                $invoiceStatus = 'PARTIALLY_PAID';
            } elseif ($statusRoll <= 9) {
                $invoiceStatus = 'ISSUED';
            } else {
                $invoiceStatus = 'OVERDUE';
            }

            // Determine how much has been paid
            $grandTotal = (float) $quotation->grand_total;
            [$amountPaid, $balanceAmount] = $this->calculatePaymentAmounts($invoiceStatus, $grandTotal);

            // If the due date is in the past and invoice is ISSUED, mark as OVERDUE
            if ($invoiceStatus === 'ISSUED' && $dueDate->isPast()) {
                $invoiceStatus = 'OVERDUE';
            }

            // Increment invoice counter for the company
            $invNumber = $company->invoice_prefix.str_pad($company->invoice_next_number, 4, '0', STR_PAD_LEFT);
            $company->increment('invoice_next_number');

            $invoice = Invoice::create([
                'company_id' => $company->id,
                'customer_id' => $customer->id,
                'quotation_id' => $quotation->id,
                'invoice_number' => $invNumber,
                'invoice_date' => $invoiceDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'subject' => $quotation->subject,
                'reference' => $quotation->quotation_number,
                'subtotal' => $quotation->subtotal,
                'discount_type' => 'NONE',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_percentage' => $quotation->tax_percentage,
                'tax_amount' => $quotation->tax_amount,
                'additional_charges' => 0,
                'grand_total' => $grandTotal,
                'amount_paid' => $amountPaid,
                'balance_amount' => $balanceAmount,
                'status' => $invoiceStatus,
                'notes' => 'Payment instructions: Please include the invoice number as payment reference.',
                'terms_conditions' => 'Payment due within 30 days.',
                'template_id' => $invTemplate->id,
                'company_snapshot' => $this->companySnapshot($company),
                'customer_snapshot' => $this->customerSnapshot($customer),
                'template_snapshot' => $this->templateSnapshot($invTemplate),
                'created_by' => $admin->id,
            ]);

            // Mark quotation as CONVERTED
            $quotation->update(['status' => 'CONVERTED']);

            // Copy line items from quotation to invoice
            foreach ($quotation->items as $qItem) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'source_quotation_item_id' => $qItem->id,
                    'sort_order' => $qItem->sort_order,
                    'item_name' => $qItem->item_name,
                    'description' => $qItem->description,
                    'quantity' => $qItem->quantity,
                    'unit' => $qItem->unit,
                    'unit_price' => $qItem->unit_price,
                    'discount_type' => 'NONE',
                    'discount_value' => 0,
                    'discount_amount' => 0,
                    'tax_percentage' => 0,
                    'tax_amount' => 0,
                    'line_total' => $qItem->line_total,
                ]);
            }

            // Create payment records
            if ($amountPaid > 0) {
                $this->createPayments($invoice, $amountPaid, $invoiceDate, $admin);
            }
        }

        // Also seed 20 standalone invoices (no quotation) for richer data
        $this->seedStandaloneInvoices($admin);
    }

    /**
     * Create standalone invoices not linked to any quotation.
     */
    private function seedStandaloneInvoices(User $admin): void
    {
        $companies = Company::with(['customers', 'templates'])->get();

        // Product/service catalogue for standalone invoices
        $products = [
            ['name' => 'Annual Support Contract',  'description' => '12-month technical support',       'unit' => 'year',  'price' => 120000],
            ['name' => 'Server Hosting',            'description' => 'Dedicated server monthly fee',     'unit' => 'month', 'price' => 35000],
            ['name' => 'Training Workshop',         'description' => 'On-site staff training (1 day)',   'unit' => 'day',   'price' => 45000],
            ['name' => 'Custom Software License',   'description' => 'Per-seat annual software licence', 'unit' => 'seat',  'price' => 15000],
            ['name' => 'Consultancy Hours',         'description' => 'Professional consulting',          'unit' => 'hour',  'price' => 8500],
        ];

        $invoiceCount = 0;
        foreach ($companies as $company) {
            if ($invoiceCount >= 20) {
                break;
            }

            $customers = $company->customers;
            if ($customers->isEmpty()) {
                continue;
            }

            $invTemplate = $company->templates->firstWhere('document_type', 'INVOICE');
            if (! $invTemplate) {
                continue;
            }

            // 4 standalone invoices per company
            for ($i = 1; $i <= 4 && $invoiceCount < 20; $i++) {
                $customer = $customers->random();
                $invoiceDate = Carbon::now()->subDays(rand(5, 200));
                $dueDate = (clone $invoiceDate)->addDays(30);

                $product = $products[array_rand($products)];
                $qty = rand(1, 5);
                $lineTotal = $qty * $product['price'];
                $taxPct = $company->vat_registered ? ($company->vat_percentage ?? 18) : 0;
                $taxAmount = round($lineTotal * $taxPct / 100, 2);
                $grandTotal = $lineTotal + $taxAmount;

                $statusRoll = rand(1, 10);
                $invoiceStatus = match (true) {
                    $statusRoll <= 4 => 'PAID',
                    $statusRoll <= 7 => 'PARTIALLY_PAID',
                    $dueDate->isPast() => 'OVERDUE',
                    default => 'ISSUED',
                };

                [$amountPaid, $balanceAmount] = $this->calculatePaymentAmounts($invoiceStatus, $grandTotal);

                $invNumber = $company->invoice_prefix.str_pad($company->invoice_next_number, 4, '0', STR_PAD_LEFT);
                $company->increment('invoice_next_number');

                $invoice = Invoice::create([
                    'company_id' => $company->id,
                    'customer_id' => $customer->id,
                    'quotation_id' => null,
                    'invoice_number' => $invNumber,
                    'invoice_date' => $invoiceDate->toDateString(),
                    'due_date' => $dueDate->toDateString(),
                    'subject' => $product['name'],
                    'reference' => 'REF-'.strtoupper(Str::random(6)),
                    'subtotal' => $lineTotal,
                    'discount_type' => 'NONE',
                    'discount_value' => 0,
                    'discount_amount' => 0,
                    'tax_percentage' => $taxPct,
                    'tax_amount' => $taxAmount,
                    'additional_charges' => 0,
                    'grand_total' => $grandTotal,
                    'amount_paid' => $amountPaid,
                    'balance_amount' => $balanceAmount,
                    'status' => $invoiceStatus,
                    'notes' => null,
                    'terms_conditions' => 'Payment due within 30 days.',
                    'template_id' => $invTemplate->id,
                    'company_snapshot' => $this->companySnapshot($company),
                    'customer_snapshot' => $this->customerSnapshot($customer),
                    'template_snapshot' => $this->templateSnapshot($invTemplate),
                    'created_by' => $admin->id,
                ]);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'source_quotation_item_id' => null,
                    'sort_order' => 1,
                    'item_name' => $product['name'],
                    'description' => $product['description'],
                    'quantity' => $qty,
                    'unit' => $product['unit'],
                    'unit_price' => $product['price'],
                    'discount_type' => 'NONE',
                    'discount_value' => 0,
                    'discount_amount' => 0,
                    'tax_percentage' => 0,
                    'tax_amount' => 0,
                    'line_total' => $lineTotal,
                ]);

                if ($amountPaid > 0) {
                    $this->createPayments($invoice, $amountPaid, $invoiceDate, $admin);
                }

                $invoiceCount++;
            }
        }
    }

    // -------------------------------------------------------------------------
    // Payment helper
    // -------------------------------------------------------------------------

    private function createPayments(Invoice $invoice, float $amountPaid, Carbon $invoiceDate, User $admin): void
    {
        $methods = ['CASH', 'BANK_TRANSFER', 'CARD', 'CHEQUE', 'OTHER'];

        // Split into 1 or 2 payments
        if ($amountPaid > 50000 && rand(0, 1)) {
            // Two partial payments
            $firstAmount = round($amountPaid * (rand(40, 70) / 100), 2);
            $secondAmount = round($amountPaid - $firstAmount, 2);

            Payment::create([
                'invoice_id' => $invoice->id,
                'payment_date' => (clone $invoiceDate)->addDays(rand(1, 10))->toDateString(),
                'amount' => $firstAmount,
                'payment_method' => $methods[array_rand($methods)],
                'reference' => 'TXN-'.strtoupper(Str::random(8)),
                'notes' => 'First instalment payment.',
                'created_by' => $admin->id,
            ]);

            Payment::create([
                'invoice_id' => $invoice->id,
                'payment_date' => (clone $invoiceDate)->addDays(rand(11, 25))->toDateString(),
                'amount' => $secondAmount,
                'payment_method' => $methods[array_rand($methods)],
                'reference' => 'TXN-'.strtoupper(Str::random(8)),
                'notes' => 'Second instalment payment.',
                'created_by' => $admin->id,
            ]);
        } else {
            Payment::create([
                'invoice_id' => $invoice->id,
                'payment_date' => (clone $invoiceDate)->addDays(rand(1, 15))->toDateString(),
                'amount' => $amountPaid,
                'payment_method' => $methods[array_rand($methods)],
                'reference' => 'TXN-'.strtoupper(Str::random(8)),
                'notes' => null,
                'created_by' => $admin->id,
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Calculation helpers
    // -------------------------------------------------------------------------

    /**
     * @return array{float, float} [amountPaid, balanceAmount]
     */
    private function calculatePaymentAmounts(string $status, float $grandTotal): array
    {
        return match ($status) {
            'PAID' => [$grandTotal, 0.00],
            'PARTIALLY_PAID' => (function () use ($grandTotal): array {
                $pct = rand(20, 80) / 100;
                $paid = round($grandTotal * $pct, 2);

                return [$paid, round($grandTotal - $paid, 2)];
            })(),
            default => [0.00, $grandTotal],
        };
    }

    /**
     * Generate 2–4 realistic line items.
     *
     * @return array{0: array<int, array<string, mixed>>, 1: float, 2: float, 3: float}
     *                                                                                  [items, subtotal, taxAmount, grandTotal]
     */
    private function generateLineItems(): array
    {
        $catalogue = [
            ['name' => 'Web Development',          'description' => 'Custom website design and development',       'unit' => 'project', 'price' => 150000],
            ['name' => 'Mobile App Development',   'description' => 'Cross-platform mobile application',          'unit' => 'project', 'price' => 350000],
            ['name' => 'UI/UX Design',             'description' => 'User interface and experience design',       'unit' => 'project', 'price' => 75000],
            ['name' => 'SEO Optimisation',         'description' => 'Search engine optimisation package',         'unit' => 'month',   'price' => 25000],
            ['name' => 'Branding Package',         'description' => 'Logo, stationery and brand guidelines',      'unit' => 'package', 'price' => 60000],
            ['name' => 'Server Setup',             'description' => 'Cloud server configuration and deployment',  'unit' => 'server',  'price' => 45000],
            ['name' => 'IT Consultation',          'description' => 'On-site IT advisory services',              'unit' => 'hour',    'price' => 7500],
            ['name' => 'Data Migration',           'description' => 'Legacy data migration to new system',        'unit' => 'project', 'price' => 120000],
            ['name' => 'Cybersecurity Audit',      'description' => 'Full penetration testing and audit',         'unit' => 'report',  'price' => 180000],
            ['name' => 'Staff Training',           'description' => 'Technical skills training workshop',         'unit' => 'day',     'price' => 35000],
            ['name' => 'Annual Maintenance',       'description' => 'System maintenance and updates (yearly)',    'unit' => 'year',    'price' => 95000],
            ['name' => 'Social Media Management',  'description' => 'Monthly social content & engagement',       'unit' => 'month',   'price' => 18000],
            ['name' => 'Email Campaign',           'description' => 'Design and deploy email marketing campaign', 'unit' => 'campaign', 'price' => 22000],
            ['name' => 'CRM Integration',          'description' => 'Integrate CRM with existing systems',       'unit' => 'project', 'price' => 200000],
            ['name' => 'Cloud Storage (100 GB)',   'description' => 'Managed cloud storage subscription',        'unit' => 'month',   'price' => 5000],
        ];

        shuffle($catalogue);
        $selectedCount = rand(2, 4);
        $selectedItems = array_slice($catalogue, 0, $selectedCount);

        $items = [];
        $subtotal = 0;

        foreach ($selectedItems as $product) {
            $qty = rand(1, 3);
            $lineTotal = $qty * $product['price'];
            $subtotal += $lineTotal;

            $items[] = [
                'name' => $product['name'],
                'description' => $product['description'],
                'qty' => $qty,
                'unit' => $product['unit'],
                'unit_price' => $product['price'],
                'line_total' => $lineTotal,
            ];
        }

        $taxAmount = 0;
        $grandTotal = $subtotal;

        return [$items, (float) $subtotal, (float) $taxAmount, (float) $grandTotal];
    }

    // -------------------------------------------------------------------------
    // Snapshot helpers (mimic what the real controllers store)
    // -------------------------------------------------------------------------

    /** @return array<string, mixed> */
    private function companySnapshot(Company $company): array
    {
        return [
            'name' => $company->name,
            'registration_number' => $company->registration_number,
            'address_line_1' => $company->address_line_1,
            'address_line_2' => $company->address_line_2,
            'city' => $company->city,
            'country' => $company->country,
            'phone' => $company->phone,
            'email' => $company->email,
            'website' => $company->website,
            'currency' => $company->currency,
            'vat_registered' => $company->vat_registered,
            'vat_number' => $company->vat_number,
            'vat_percentage' => $company->vat_percentage,
        ];
    }

    /** @return array<string, mixed> */
    private function customerSnapshot(Customer $customer): array
    {
        return [
            'customer_name' => $customer->customer_name,
            'business_name' => $customer->business_name,
            'registration_number' => $customer->registration_number,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address_line_1' => $customer->address_line_1,
            'address_line_2' => $customer->address_line_2 ?? null,
            'city' => $customer->city,
            'country' => $customer->country,
        ];
    }

    /** @return array<string, mixed> */
    private function templateSnapshot(CompanyTemplate $template): array
    {
        return [
            'template_name' => $template->template_name,
            'document_type' => $template->document_type,
            'header_text' => $template->header_text,
            'footer_text' => $template->footer_text,
            'terms_conditions' => $template->terms_conditions,
        ];
    }

    // -------------------------------------------------------------------------
    // Random content helpers
    // -------------------------------------------------------------------------

    private function randomSubject(): string
    {
        $subjects = [
            'Digital Transformation Project',
            'IT Infrastructure Upgrade',
            'Website Redesign & SEO',
            'Mobile Application Development',
            'Annual Software Maintenance',
            'Data Analytics Platform',
            'Cybersecurity Assessment',
            'Cloud Migration Services',
            'E-Commerce Platform Build',
            'CRM System Implementation',
            'Staff Training Programme',
            'Brand Identity Refresh',
            'Social Media Marketing',
            'Network Infrastructure Setup',
            'ERP Integration Project',
        ];

        return $subjects[array_rand($subjects)];
    }

    private function randomProjectName(): string
    {
        $prefixes = ['Project', 'Initiative', 'Phase', 'Programme', 'Engagement'];
        $names = ['Alpha', 'Beta', 'Horizon', 'Velocity', 'Pinnacle', 'Nova', 'Apex', 'Zenith', 'Pulse', 'Titan'];

        return $prefixes[array_rand($prefixes)].' '.$names[array_rand($names)];
    }
}
