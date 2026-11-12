<?php
/**
 * Contact Us Page — Amethystclothing.lk
 * Sharp Minimalist Luxury / High-Contrast Streetwear
 */
$pageTitle = 'Contact Us — Amethystclothing.lk | Hotline, WhatsApp & Support';
$whatsappClean = '94770000000';
$contactEmail = get_setting('contact_email', 'hello@amethystclothing.lk');

// Handle contact form submission
$formSuccess = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senderName    = trim($_POST['name'] ?? '');
    $senderContact = trim($_POST['contact'] ?? '');
    $senderSubject = trim($_POST['subject'] ?? '');
    $senderMessage = trim($_POST['message'] ?? '');

    if (empty($senderName) || empty($senderContact) || empty($senderMessage)) {
        $formError = 'Please fill out all required fields.';
    } else {
        // Record inquiry or send notification
        $formSuccess = true;
    }
}
?>
<section class="section" style="padding-top: 50px;">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumb-nav">
            <a href="<?= base_url() ?>">HOME</a>
            <span>/</span>
            <strong>CONTACT US</strong>
        </nav>

        <!-- Section Header -->
        <div style="border-bottom: 1px solid var(--border-crisp); padding-bottom: 32px; margin-bottom: 48px;">
            <span style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.16em; text-transform: uppercase; color: var(--brand-deep);">
                COMMUNICATION // DIRECT DISPATCH
            </span>
            <h1 style="font-family: var(--font-display); font-size: clamp(32px, 5vw, 48px); margin: 8px 0 16px; text-transform: uppercase; letter-spacing: -0.02em;">
                CONNECT WITH AMETHYST
            </h1>
            <p style="font-size: 15px; color: var(--text-secondary); max-width: 680px; line-height: 1.6;">
                Have an inquiry about sizing, bulk custom T-shirt printing, or an existing order? Get in touch through our direct channels or send a message below.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 48px; align-items: start;" class="contact-layout">
            <!-- Left: Contact Form -->
            <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 36px;">
                <h3 style="font-family: var(--font-display); font-size: 18px; text-transform: uppercase; margin: 0 0 8px;">
                    SEND A DIRECT MESSAGE
                </h3>
                <p style="font-size: 13px; color: var(--text-secondary); margin: 0 0 24px;">
                    Our operations desk reviews all inquiries and replies via phone or email within 24 hours.
                </p>

                <?php if ($formSuccess): ?>
                    <div style="padding: 16px; background: #D1FAE5; border: 1px solid #A7F3D0; color: #065F46; font-size: 13px; margin-bottom: 24px;">
                        <strong>Thank you!</strong> Your message has been received. Our team will get in touch with you shortly.
                    </div>
                <?php elseif ($formError): ?>
                    <div style="padding: 16px; background: #FEE2E2; border: 1px solid #FECACA; color: #991B1B; font-size: 13px; margin-bottom: 24px;">
                        <?= e($formError) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="contact-form">
                    <div style="margin-bottom: 18px;">
                        <label style="display:block; font-family:var(--font-mono); font-size:11px; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px;">
                            YOUR FULL NAME <span style="color:#EF4444;">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="e.g. Shanuka Perera" 
                               style="width:100%; padding:10px 12px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; box-sizing:border-box;">
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display:block; font-family:var(--font-mono); font-size:11px; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px;">
                            EMAIL OR MOBILE NUMBER <span style="color:#EF4444;">*</span>
                        </label>
                        <input type="text" name="contact" required placeholder="e.g. 077XXXXXXX or hello@domain.com" 
                               style="width:100%; padding:10px 12px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; box-sizing:border-box;">
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display:block; font-family:var(--font-mono); font-size:11px; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px;">
                            INQUIRY SUBJECT
                        </label>
                        <select name="subject" style="width:100%; padding:10px 12px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; background:#FFF; box-sizing:border-box;">
                            <option value="Order Status">Order Status &amp; Tracking</option>
                            <option value="Size Exchange">14-Day Size Exchange Request</option>
                            <option value="Custom Printing">Custom T-Shirt Printing / Bulk Order</option>
                            <option value="Wholesale">Wholesale &amp; Retail Inquiries</option>
                            <option value="Other">General Question</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 24px;">
                        <label style="display:block; font-family:var(--font-mono); font-size:11px; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px;">
                            MESSAGE <span style="color:#EF4444;">*</span>
                        </label>
                        <textarea name="message" rows="5" required placeholder="Describe your inquiry, order number, or custom printing requirements..." 
                                  style="width:100%; padding:10px 12px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; box-sizing:border-box;"></textarea>
                    </div>

                    <button type="submit" class="btn btn--brand btn--full" style="padding: 14px; font-size: 12px; letter-spacing: 0.1em; border-radius:0;">
                        TRANSMIT MESSAGE &rarr;
                    </button>
                </form>
            </div>

            <!-- Right: Direct Channels & Social Hub -->
            <div style="display:flex; flex-direction:column; gap:24px;">
                <!-- Fast Response Box -->
                <div style="background: #0B0910; color: #FFFFFF; border: 1px solid #231E2C; padding: 32px;">
                    <span style="font-family: var(--font-mono); font-size: 11px; color: #10B981; letter-spacing: 0.14em; text-transform: uppercase;">
                        FASTEST RESPONSE CHANNEL
                    </span>
                    <h3 style="font-family: var(--font-display); font-size: 20px; text-transform: uppercase; margin: 8px 0 12px;">
                        WHATSAPP CONCIERGE
                    </h3>
                    <p style="font-size: 13px; color: #A49EB2; line-height: 1.6; margin: 0 0 20px;">
                        For immediate doorstep size exchanges, instant stock checks, or custom apparel design mockups:
                    </p>
                    <a href="https://wa.me/<?= e($whatsappClean) ?>?text=<?= urlencode('Hi Amethyst! I have an inquiry.') ?>" target="_blank" rel="noopener" 
                       class="btn btn--primary btn--full" style="padding: 12px; background: #10B981; border-color: #10B981; color:#0B0910; font-weight:700;">
                        CHAT ON WHATSAPP &rarr;
                    </a>
                </div>

                <!-- Contact Details List -->
                <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 32px;">
                    <h4 style="font-family: var(--font-display); font-size: 15px; text-transform: uppercase; margin: 0 0 18px;">
                        OFFICIAL COMMUNICATION LINES
                    </h4>
                    <div style="display:flex; flex-direction:column; gap:16px; font-size:13px;">
                        <div>
                            <div style="font-family:var(--font-mono); font-size:10px; color:#8C869B; text-transform:uppercase;">CUSTOMER HOTLINE</div>
                            <a href="tel:+94770000000" style="color:var(--text-primary); font-weight:700; text-decoration:none;">+94 77 000 0000</a>
                        </div>
                        <div>
                            <div style="font-family:var(--font-mono); font-size:10px; color:#8C869B; text-transform:uppercase;">DIRECT EMAIL</div>
                            <a href="mailto:<?= e($contactEmail) ?>" style="color:var(--brand-deep); font-weight:700; text-decoration:none;"><?= e($contactEmail) ?></a>
                        </div>
                        <div>
                            <div style="font-family:var(--font-mono); font-size:10px; color:#8C869B; text-transform:uppercase;">VIP WHATSAPP GROUP</div>
                            <a href="https://chat.whatsapp.com/invite/amethystclothingvip" target="_blank" rel="noopener" style="color:var(--brand-deep); font-weight:700; text-decoration:underline;">Join Amethyst VIP Community &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Verified Social Channels -->
                <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 24px;">
                    <div style="font-family:var(--font-mono); font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:12px;">VERIFIED SOCIAL HUBS</div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px;">
                        <a href="https://instagram.com/amethystclothing.lk" target="_blank" rel="noopener" class="btn btn--outline btn--sm" style="font-size:11px;">Instagram</a>
                        <a href="https://facebook.com/amethystclothing.lk" target="_blank" rel="noopener" class="btn btn--outline btn--sm" style="font-size:11px;">Facebook</a>
                        <a href="https://tiktok.com/@amethystclothing.lk" target="_blank" rel="noopener" class="btn btn--outline btn--sm" style="font-size:11px;">TikTok</a>
                        <a href="https://youtube.com/@amethystclothinglk" target="_blank" rel="noopener" class="btn btn--outline btn--sm" style="font-size:11px;">YouTube</a>
                        <a href="https://discord.gg/amethystclothing" target="_blank" rel="noopener" class="btn btn--outline btn--sm" style="font-size:11px;">Discord</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
