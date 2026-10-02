<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Franchise & Partnerships page content V1 and its two form texts — OWNER APPROVED (M47 Parts 1–5, 2026-10-02), verbatim.
 * Inserted once: a page or text that already exists is never overwritten (later edits belong to the Owner Dashboard).
 * Sections the Owner sent in Arabic only stay hidden until their English arrives (LANGUAGE-PARITY, FRAN-018), and the
 * nine "why partner" pillars stay hidden until SHELTER Franchise Master confirms them (PENDING VERIFICATION — FRAN-026).
 */
class FranchiseSeeder extends Seeder
{
    public const ACKNOWLEDGEMENT_AR = 'أفهم أن تقديم هذا الطلب يعبر عن اهتمامي بفرصة شراكة محتملة مع SHELTER COFFEE فقط، ولا يعد موافقة على منح امتياز أو شراكة، ولا يشكل عرضًا أو اتفاقًا أو التزامًا تعاقديًا أو تجاريًا على SHELTER COFFEE أو على مقدم الطلب. تخضع جميع الطلبات للمراجعة والتقييم قبل الانتقال إلى أي مرحلة لاحقة.';

    public const ACKNOWLEDGEMENT_EN = 'I understand that submitting this application only expresses my interest in a potential partnership opportunity with SHELTER COFFEE. It does not constitute approval of a franchise or partnership and does not create an offer, agreement, or contractual or commercial obligation for SHELTER COFFEE or the applicant. All applications are subject to review and evaluation before proceeding to any further stage.';

    public const CONSENT_AR = "أقر بأن المعلومات التي قدمتها في هذا الطلب صحيحة حسب علمي، وأوافق على قيام SHELTER COFFEE بجمع واستخدام وتخزين ومعالجة البيانات التي أقدمها لغرض مراجعة طلب الشراكة، تقييم الفرصة، والتواصل معي بشأن الطلب ومراحله.\n\nلا تعتبر هذه الموافقة اشتراكًا في الرسائل التسويقية.";

    public const CONSENT_EN = "I confirm that the information I have provided in this application is accurate to the best of my knowledge.\n\nI consent to SHELTER COFFEE collecting, using, storing, and processing the information I provide for the purpose of reviewing my partnership application, evaluating the opportunity, and contacting me regarding the application and its stages.\n\nThis consent does not constitute an opt-in to marketing communications.";

    public function run(): void
    {
        $now = now();
        foreach ([
            ['partnership-ack-v1', 'partnership_ack', self::ACKNOWLEDGEMENT_AR, self::ACKNOWLEDGEMENT_EN],
            ['partnership-consent-v1', 'partnerships', self::CONSENT_AR, self::CONSENT_EN],
        ] as [$version, $scope, $ar, $en]) {
            DB::table('consent_versions')->insertOrIgnore([
                'version' => $version, 'scope' => $scope, 'text_ar' => $ar, 'text_en' => $en,
                'active_from' => $now, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        if (Page::query()->where('key', 'franchise')->exists()) {
            return;
        }
        $page = Page::query()->create([
            'key' => 'franchise',
            'type' => 'landing',
            'title_ar' => "كن شريكًا في نمو\nSHELTER COFFEE",
            'title_en' => "Grow With\nSHELTER COFFEE",
            'name_ar' => 'كن شريكًا مع SHELTER COFFEE',
            'name_en' => 'Partner With SHELTER COFFEE',
            'description_ar' => 'انطلقت SHELTER COFFEE من إربد، الأردن عام 2019. نواصل تطوير تجربة تجمع بين القهوة، هوية العلامة، جودة التشغيل وتجربة العميل، ونرحب بالمهتمين بدراسة فرص الشراكة معنا في الأسواق المناسبة.',
            'description_en' => 'SHELTER COFFEE was founded in Irbid, Jordan in 2019. We continue to develop a coffee experience built around brand identity, operating standards, product quality, and customer experience, and we welcome serious partnership interest from suitable markets.',
            'status' => PublishStatus::Published,
            'origin' => 'owner',
            'published_at' => $now,
            'content_updated_at' => $now,
        ]);

        $sections = [
            ['text', 'من هي SHELTER؟', 'Who Is SHELTER?',
                'SHELTER COFFEE هي علامة قهوة انطلقت من إربد، الأردن عام 2019. تطورت تجربة العلامة من خلال نماذجها الحالية SHELTER COFFEE DRIVE وSHELTER COFFEE HOUSE، مع التركيز على تجربة العميل، جودة المنتج، وضوح الهوية ومعايير التشغيل.',
                'SHELTER COFFEE is a coffee brand founded in Irbid, Jordan in 2019. The brand currently operates through the SHELTER COFFEE DRIVE and SHELTER COFFEE HOUSE concepts, with a focus on customer experience, product quality, brand consistency, and operating standards.'],
            // Current brand experiences — never described as franchise packages (M47 Part 1, FRAN-016).
            ['cards', 'نماذج تجربة SHELTER الحالية', 'Current SHELTER Experiences',
                "SHELTER COFFEE DRIVE\nتجربة SHELTER المصممة لتقديم خدمة سريعة وسهلة مع الحفاظ على جودة المنتج وهوية العلامة.\n\nSHELTER COFFEE HOUSE\nتجربة مقهى تتيح للضيوف الاستمتاع بمنتجات SHELTER ضمن بيئة تعكس هوية العلامة وتجربتها.",
                "SHELTER COFFEE DRIVE\nA SHELTER experience designed around convenience and speed while maintaining product quality and brand consistency.\n\nSHELTER COFFEE HOUSE\nA café experience where guests can enjoy SHELTER products in an environment that reflects the brand and its experience."],
            // Arabic only so far — hidden until its English is approved.
            ['text', 'لماذا تصبح شريكًا مع SHELTER؟', null,
                'ننظر إلى الشراكة على أنها أكثر من مجرد استخدام اسم تجاري. الهدف هو بناء تجربة تحافظ على هوية SHELTER ومعاييرها وتقدمها بصورة متناسقة في كل سوق.',
                null, false],
            // The nine pillars: PENDING VERIFICATION against SHELTER Franchise Master — never a public promise before.
            ['list', null, null,
                "هوية وتجربة العلامة\nنظام القهوة والمنيو\nمعايير التشغيل\nمعايير الجودة\nالتدريب والتأهيل\nدعم العلامة والتسويق\nالتوريد والمشتريات\nتصميم وتجربة الموقع\nالأنظمة والتقنية",
                "Brand & Customer Experience\nCoffee & Menu System\nOperational Standards\nQuality Standards\nTraining\nBrand & Marketing Support\nSupply & Procurement\nStore Design & Experience\nTechnology & Systems", false],
            ['text', 'أكثر من مجرد اسم على الواجهة', 'More Than a Name on the Storefront',
                'الشراكة مع SHELTER لا تقتصر على استخدام الاسم أو الشعار. نحن ننظر إلى تجربة العلامة كمنظومة تشمل المنتج، تجربة العميل، الهوية، معايير التشغيل والجودة، والتدريب وفق النموذج الذي يتم اعتماده لكل شراكة.',
                'A SHELTER partnership is more than the use of a name or logo. We view the brand as a complete experience that includes the product, customer experience, visual identity, operating and quality standards, and training according to the model approved for each partnership.'],
            // Arabic only so far — hidden until its English is approved.
            ['text', 'ما الذي نبحث عنه في الشريك؟', null,
                'نبحث عن شركاء جادين يقدرون أهمية الجودة، الالتزام بالمعايير وفهم السوق المحلي، ولديهم الرغبة في بناء علاقة طويلة المدى مع SHELTER COFFEE.',
                null, false],
            ['list', null, null,
                "الالتزام بهوية ومعايير SHELTER\nالجدية في الاستثمار والتشغيل\nالقدرة الإدارية المناسبة\nفهم السوق المحلي\nالالتزام بمعايير الجودة\nالالتزام بالنظام التشغيلي المعتمد\nالرغبة في بناء علاقة طويلة المدى",
                null, false],
            ['steps', 'رحلة الشراكة', 'Partnership Journey',
                "تقديم طلب الاهتمام\nالمراجعة الأولية\nاجتماع تعريفي\nتقييم السوق والموقع\nمناقشة نموذج الشراكة\nالموافقات\nالتعاقد\nالتجهيز والتدريب\nالافتتاح",
                "Submit Your Interest\nInitial Review\nIntroductory Meeting\nMarket & Location Review\nPartnership Model Discussion\nApprovals\nAgreement\nSetup & Training\nOpening"],
            ['text', 'أسواق النمو', 'Growth Markets',
                'ندرس فرص النمو داخل الأردن وخارجه وفق ملاءمة السوق، الموقع وطبيعة الشريك المحتمل. تقديم الطلب لا يعني أن السوق أو المنطقة المطلوبة متاحة أو محجوزة.',
                'We evaluate growth opportunities in Jordan and international markets based on market suitability, location, and the potential partner. Submitting an application does not mean that a requested market or territory is available or reserved.'],
            ['faq', 'هل يمكن التقديم من داخل الأردن؟', 'Can I apply from Jordan?',
                'نعم. يمكن تقديم طلب اهتمام بالشراكة من داخل الأردن، ويخضع الطلب للمراجعة والتقييم.',
                'Yes. Partnership interest may be submitted from Jordan and is subject to review and evaluation.'],
            ['faq', 'هل يمكن التقديم من خارج الأردن؟', 'Can I apply from outside Jordan?',
                'نعم. يمكن تقديم طلب اهتمام من خارج الأردن، ويتم تقييم كل سوق وفرصة بشكل مستقل.',
                'Yes. International partnership interest may be submitted, and each market and opportunity is evaluated individually.'],
            ['faq', 'هل يجب أن تكون لدي خبرة سابقة في قطاع المقاهي؟', 'Do I need previous café experience?',
                'يمكن أن تكون الخبرة التجارية أو التشغيلية عاملًا مهمًا في التقييم، لكن تتم دراسة الطلب بشكل متكامل وفق طبيعة السوق، الشريك والفرصة المقترحة.',
                'Commercial or operational experience may be relevant to the evaluation, but each application is considered as a whole based on the market, partner profile, and proposed opportunity.'],
            ['faq', 'كيف يتم تقييم الموقع المقترح؟', 'How is a proposed location evaluated?',
                'يتم تقييم المواقع خلال المراحل اللاحقة من الدراسة وفق عوامل مرتبطة بالسوق، الموقع ونموذج التشغيل المقترح.',
                'Proposed locations are reviewed during later stages based on factors related to the market, site, and proposed operating model.'],
            ['faq', 'ماذا يحدث بعد إرسال الطلب؟', 'What happens after I submit my application?',
                'يقوم فريق SHELTER بمراجعة المعلومات المقدمة. إذا كانت الفرصة مناسبة للانتقال إلى مرحلة أخرى، يتم التواصل مع مقدم الطلب لمناقشة الخطوات التالية.',
                'The SHELTER team reviews the submitted information. If the opportunity is suitable for further consideration, the applicant will be contacted regarding the next stage.'],
            ['faq', 'ما تكلفة الحصول على فرنشايز SHELTER؟', 'How much does a SHELTER franchise cost?',
                'تتم مناقشة الرسوم، المتطلبات والتفاصيل الاستثمارية مع المتقدمين المؤهلين خلال المراحل اللاحقة بعد تقييم السوق والفرصة.',
                'Fees, investment requirements, and commercial terms are discussed with qualified applicants at later stages after the market and opportunity have been evaluated.'],
            ['faq', 'هل تضمن SHELTER أرباحًا أو عائدًا معينًا؟', 'Does SHELTER guarantee profit or a specific return?',
                'لا يتم تقديم ضمان للأرباح أو العوائد. تعتمد النتائج التجارية على مجموعة من العوامل التشغيلية والسوقية والإدارية.',
                'No profit or specific return is guaranteed. Business results depend on a range of operational, market, and management factors.'],
            ['cta', 'مهتم ببناء SHELTER في سوقك؟', 'Interested in Bringing SHELTER to Your Market?',
                'ابدأ بإرسال معلوماتك الأساسية وسنراجع فرصة الشراكة قبل الانتقال إلى أي مرحلة لاحقة.',
                'Start by sharing the key information about your interest and proposed market. The opportunity will be reviewed before any further stage.'],
        ];
        foreach ($sections as $sort => $section) {
            [$type, $headingAr, $headingEn, $bodyAr, $bodyEn] = $section;
            $page->sections()->create([
                'type' => $type, 'heading_ar' => $headingAr, 'heading_en' => $headingEn, 'body_ar' => $bodyAr, 'body_en' => $bodyEn,
                'origin' => 'owner', 'is_visible' => $section[5] ?? true, 'sort' => $sort + 1,
            ]);
        }
    }
}
