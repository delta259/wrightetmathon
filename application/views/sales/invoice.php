<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture <?php echo htmlspecialchars($sale_id); ?></title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.4;
            background: #f0f4f8;
        }
        .invoice-page {
            max-width: 210mm;
            margin: 20px auto;
            background: #fff;
            padding: 20mm;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }
        .invoice-actions {
            max-width: 210mm;
            margin: 20px auto 0;
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .invoice-actions button,
        .invoice-actions a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
        }
        .btn-print {
            background: #0A6184;
        }
        .btn-print:hover {
            background: #085570;
        }
        .btn-back {
            background: #64748b;
        }
        .btn-back:hover {
            background: #475569;
        }
        @media print {
            body {
                background: #fff;
            }
            .invoice-page {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .invoice-actions {
                display: none !important;
            }
        }

        /* Header */
        .inv-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .inv-company {
            flex: 1;
        }
        .inv-company-logo {
            margin-bottom: 8px;
        }
        .inv-company-logo img {
            max-height: 50px;
            object-fit: contain;
        }
        .inv-company-name {
            font-size: 20px;
            font-weight: 700;
            color: #0A6184;
            margin-bottom: 6px;
        }
        .inv-company-detail {
            font-size: 11px;
            color: #475569;
            line-height: 1.6;
        }
        .inv-title-block {
            text-align: right;
        }
        .inv-title {
            font-size: 24px;
            font-weight: 700;
            color: #0A6184;
            margin-bottom: 4px;
        }
        .inv-title-date {
            font-size: 12px;
            color: #475569;
        }

        /* Customer */
        .inv-customer-block {
            margin-bottom: 25px;
            display: flex;
            justify-content: flex-end;
        }
        .inv-customer {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 14px 18px;
            min-width: 250px;
        }
        .inv-customer-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .inv-customer-name {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 2px;
        }
        .inv-customer-detail {
            font-size: 11px;
            color: #475569;
            line-height: 1.5;
        }

        /* Table */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .inv-table thead th {
            background: #4386a1;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 8px 10px;
            text-align: left;
            border: none;
        }
        .inv-table thead th:first-child {
            border-radius: 4px 0 0 0;
        }
        .inv-table thead th:last-child {
            border-radius: 0 4px 0 0;
        }
        .inv-table thead th.num {
            text-align: right;
        }
        .inv-table thead th.center {
            text-align: center;
        }
        .inv-table tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            vertical-align: top;
        }
        .inv-table tbody td.num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }
        .inv-table tbody td.center {
            text-align: center;
        }
        .inv-table tbody tr:last-child td {
            border-bottom: 2px solid #4386a1;
        }

        /* TVA recap + totals */
        .inv-bottom {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            margin-bottom: 25px;
        }
        .inv-tva-table {
            width: auto;
            border-collapse: collapse;
        }
        .inv-tva-table th {
            background: #e2e8f0;
            color: #334155;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 6px 12px;
            text-align: right;
            border: none;
        }
        .inv-tva-table th:first-child {
            border-radius: 4px 0 0 0;
            text-align: left;
        }
        .inv-tva-table th:last-child {
            border-radius: 0 4px 0 0;
        }
        .inv-tva-table td {
            padding: 5px 12px;
            font-size: 11px;
            text-align: right;
            border-bottom: 1px solid #f1f5f9;
        }
        .inv-tva-table td:first-child {
            text-align: left;
        }

        .inv-totals {
            min-width: 220px;
        }
        .inv-total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 12px;
        }
        .inv-total-row.inv-total-main {
            border-top: 2px solid #0A6184;
            margin-top: 4px;
            padding-top: 8px;
            font-size: 16px;
            font-weight: 700;
            color: #0A6184;
        }
        .inv-total-label {
            color: #475569;
        }
        .inv-total-value {
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        /* Payments */
        .inv-payments {
            margin-bottom: 25px;
            padding: 10px 14px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .inv-payments-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .inv-payment-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding: 2px 0;
        }

        /* Legal */
        .inv-legal {
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            font-size: 9px;
            color: #94a3b8;
            line-height: 1.6;
        }
        .inv-legal p {
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

<div class="invoice-actions">
    <button class="btn-print" onclick="window.print();">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Imprimer
    </button>
    <a class="btn-back" href="javascript:window.close();">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Fermer
    </a>
</div>

<div class="invoice-page">

    <!-- Header: Company + Invoice title -->
    <div class="inv-header">
        <div class="inv-company">
            <?php
            $logo_path = FCPATH . 'images/yes-store-logo.jpg';
            if (file_exists($logo_path)): ?>
            <div class="inv-company-logo"><img src="<?php echo base_url('images/yes-store-logo.jpg'); ?>" alt="<?php echo htmlspecialchars($company); ?>"></div>
            <?php endif; ?>
            <div class="inv-company-name"><?php echo htmlspecialchars($company); ?></div>
            <div class="inv-company-detail">
                <?php echo nl2br(htmlspecialchars($address)); ?><br>
                <?php if (!empty($phone)): ?>T&eacute;l : <?php echo htmlspecialchars($phone); ?><br><?php endif; ?>
                <?php if (!empty($siret)): ?>SIRET : <?php echo htmlspecialchars($siret); ?><br><?php endif; ?>
                <?php if (!empty($tva_intra)): ?>TVA Intracommunautaire : <?php echo htmlspecialchars($tva_intra); ?><br><?php endif; ?>
                <?php if (!empty($rcs)): ?><?php echo htmlspecialchars($rcs); ?><br><?php endif; ?>
            </div>
        </div>
        <div class="inv-title-block">
            <div class="inv-title">FACTURE</div>
            <div class="inv-title-date">
                N&deg; <?php echo htmlspecialchars($sale_id); ?><br>
                Date : <?php echo $transaction_time; ?>
            </div>
        </div>
    </div>

    <!-- Customer -->
    <div class="inv-customer-block">
        <div class="inv-customer">
            <div class="inv-customer-label">Client</div>
            <?php if (isset($customer) && $customer): ?>
                <?php if (!empty($customer->company_name)): ?>
                <div class="inv-customer-name"><?php echo htmlspecialchars($customer->company_name); ?></div>
                <div class="inv-customer-detail" style="margin-bottom:3px;"><?php echo htmlspecialchars(trim($customer->first_name . ' ' . $customer->last_name)); ?></div>
                <?php else: ?>
                <div class="inv-customer-name"><?php echo htmlspecialchars(trim($customer->first_name . ' ' . $customer->last_name)); ?></div>
                <?php endif; ?>
                <div class="inv-customer-detail">
                    <?php if (!empty($customer->address_1)): ?><?php echo htmlspecialchars($customer->address_1); ?><br><?php endif; ?>
                    <?php if (!empty($customer->address_2)): ?><?php echo htmlspecialchars($customer->address_2); ?><br><?php endif; ?>
                    <?php if (!empty($customer->zip) || !empty($customer->city)): ?>
                        <?php echo htmlspecialchars(trim($customer->zip . ' ' . $customer->city)); ?><br>
                    <?php endif; ?>
                    <?php if (!empty($customer->state)): ?><?php echo htmlspecialchars($customer->state); ?><br><?php endif; ?>
                    <?php if (!empty($customer->email)): ?><?php echo htmlspecialchars($customer->email); ?><?php endif; ?>
                </div>
            <?php else: ?>
                <div class="inv-customer-name">Client anonyme</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Articles table -->
    <?php
    // Build TVA recap and totals from cart lines
    $tva_recap = array();
    $total_ht = 0;
    ?>
    <table class="inv-table">
        <thead>
            <tr>
                <th style="width:15%">R&eacute;f&eacute;rence</th>
                <th style="width:35%">D&eacute;signation</th>
                <th class="center" style="width:8%">Qt&eacute;</th>
                <th class="num" style="width:12%">PU HT</th>
                <th class="center" style="width:8%">Remise</th>
                <th class="num" style="width:10%">TVA %</th>
                <th class="num" style="width:12%">Total HT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart as $line): ?>
            <?php
                $pu_ht = (float)$line['item_unit_price'];
                $qty = (float)$line['quantity_purchased'];
                $discount = (float)$line['discount_percent'];
                $tax_pct = (float)$line['line_tax_percentage'];

                $line_ht = $pu_ht * $qty * (1 - $discount / 100);
                $total_ht += $line_ht;

                // Accumulate TVA recap
                $tax_key = number_format($tax_pct, 2);
                if (!isset($tva_recap[$tax_key])) {
                    $tva_recap[$tax_key] = array('base_ht' => 0, 'taux' => $tax_pct);
                }
                $tva_recap[$tax_key]['base_ht'] += $line_ht;
            ?>
            <tr>
                <td><?php echo htmlspecialchars($line['line_item_number']); ?></td>
                <td><?php echo htmlspecialchars($line['line_name']); ?></td>
                <td class="center"><?php echo $qty == (int)$qty ? (int)$qty : number_format($qty, 2, ',', ''); ?></td>
                <td class="num"><?php echo number_format($pu_ht, 2, ',', ' '); ?> &euro;</td>
                <td class="center"><?php echo $discount > 0 ? number_format($discount, 1, ',', '') . ' %' : ''; ?></td>
                <td class="num"><?php echo number_format($tax_pct, 1, ',', '') . ' %'; ?></td>
                <td class="num"><?php echo number_format($line_ht, 2, ',', ' '); ?> &euro;</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- TVA recap + Totals -->
    <?php
    $total_tva = 0;
    foreach ($tva_recap as $key => &$recap) {
        $recap['montant_tva'] = $recap['base_ht'] * $recap['taux'] / 100;
        $total_tva += $recap['montant_tva'];
    }
    unset($recap);
    $total_ttc = $total_ht + $total_tva;
    ksort($tva_recap);
    ?>
    <div class="inv-bottom">
        <!-- TVA recap table -->
        <div>
            <table class="inv-tva-table">
                <thead>
                    <tr>
                        <th>Taux TVA</th>
                        <th>Base HT</th>
                        <th>Montant TVA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tva_recap as $key => $recap): ?>
                    <tr>
                        <td><?php echo number_format($recap['taux'], 1, ',', '') . ' %'; ?></td>
                        <td><?php echo number_format($recap['base_ht'], 2, ',', ' '); ?> &euro;</td>
                        <td><?php echo number_format($recap['montant_tva'], 2, ',', ' '); ?> &euro;</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="inv-totals">
            <div class="inv-total-row">
                <span class="inv-total-label">Total HT</span>
                <span class="inv-total-value"><?php echo number_format($total_ht, 2, ',', ' '); ?> &euro;</span>
            </div>
            <div class="inv-total-row">
                <span class="inv-total-label">Total TVA</span>
                <span class="inv-total-value"><?php echo number_format($total_tva, 2, ',', ' '); ?> &euro;</span>
            </div>
            <div class="inv-total-row inv-total-main">
                <span>Total TTC</span>
                <span><?php echo number_format($total_ttc, 2, ',', ' '); ?> &euro;</span>
            </div>
        </div>
    </div>

    <!-- Payments -->
    <?php if (!empty($payments)): ?>
    <div class="inv-payments">
        <div class="inv-payments-title">R&egrave;glement</div>
        <?php foreach ($payments as $payment): ?>
        <div class="inv-payment-row">
            <span><?php echo htmlspecialchars($payment['payment_method_description'] ?? $payment['payment_method_code']); ?></span>
            <span><?php echo number_format((float)$payment['payment_amount'], 2, ',', ' '); ?> &euro;</span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Legal mentions -->
    <div class="inv-legal">
        <p><strong>Conditions de r&egrave;glement :</strong> Paiement comptant.</p>
        <p>En cas de retard de paiement, une p&eacute;nalit&eacute; de retard au taux annuel de 3 fois le taux d'int&eacute;r&ecirc;t l&eacute;gal sera appliqu&eacute;e, conform&eacute;ment &agrave; l'article L.441-10 du Code de commerce.</p>
        <p>Indemnit&eacute; forfaitaire pour frais de recouvrement : 40 &euro; (art. D.441-5 du Code de commerce).</p>
        <p>Pas d'escompte pour paiement anticip&eacute;.</p>
        <?php if (!empty($siret)): ?>
        <p style="margin-top:8px;"><?php echo htmlspecialchars($company); ?> &mdash; SIRET <?php echo htmlspecialchars($siret); ?><?php if (!empty($tva_intra)): ?> &mdash; TVA <?php echo htmlspecialchars($tva_intra); ?><?php endif; ?><?php if (!empty($rcs)): ?> &mdash; <?php echo htmlspecialchars($rcs); ?><?php endif; ?></p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
