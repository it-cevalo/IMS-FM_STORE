<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTH & DASHBOARD
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| MASTER DATA
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Master\CustomerController;
use App\Http\Controllers\Master\WarehouseController;
use App\Http\Controllers\Master\StoreController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Master\CourierController;
use App\Http\Controllers\Master\ProductUnitController;
use App\Http\Controllers\Master\ProductTypeController;
use App\Http\Controllers\Master\SKUController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\BankController;

/*
|--------------------------------------------------------------------------
| TRANSAKSI
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Transaction\PurchaseOrderController;
use App\Http\Controllers\Transaction\PurchaseRequestController;
use App\Http\Controllers\Transaction\DeliveryOrderController;
use App\Http\Controllers\Transaction\DeliveryOrderTransferController;
use App\Http\Controllers\Transaction\PaymentController;
use App\Http\Controllers\Transaction\ProductInboundController;
use App\Http\Controllers\Transaction\ProductOutboundController;

/*
|--------------------------------------------------------------------------
| INVOICE
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Invoice\InvoiceController;
use App\Http\Controllers\Invoice\TaxInvoiceController;
use App\Http\Controllers\Invoice\ReceiptInvoiceController;
use App\Http\Controllers\Invoice\SendInvoiceController;

/*
|--------------------------------------------------------------------------
| REPORT
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Report\ReportStockMovementController;
use App\Http\Controllers\Report\ReportPaymentController;
use App\Http\Controllers\Report\ReportInvoicingController;
use App\Http\Controllers\Report\ReportCustomerController;
use App\Http\Controllers\Report\ReportCourierController;
use App\Http\Controllers\Report\ReportStockMutationController;

/*
|--------------------------------------------------------------------------
| STOCK
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Stock\StockOpnameController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

Auth::routes();

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD & USER MANAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);

    /*
    |--------------------------------------------------------------------------
    | MASTER - BANK
    |--------------------------------------------------------------------------
    */
    Route::get('/bank-data', [BankController::class, 'getData'])->name('bank.data');
    Route::resource('bank', BankController::class);

    /*
    |--------------------------------------------------------------------------
    | MASTER - CUSTOMER
    |--------------------------------------------------------------------------
    */
    Route::get('/cust-data', [CustomerController::class, 'getData'])->name('customers.data');
    Route::resource('customers', CustomerController::class);

    /*
    |--------------------------------------------------------------------------
    | MASTER - SUPPLIER
    |--------------------------------------------------------------------------
    */
    Route::get('/suppliers/data', [SupplierController::class, 'getData'])->name('suppliers.data');
    Route::resource('suppliers', SupplierController::class);

    /*
    |--------------------------------------------------------------------------
    | MASTER - COURIER
    |--------------------------------------------------------------------------
    */
    Route::get('/couriers/data', [CourierController::class, 'getData'])->name('couriers.data');
    Route::resource('couriers', CourierController::class);

    /*
    |--------------------------------------------------------------------------
    | MASTER - WAREHOUSE & STORE
    |--------------------------------------------------------------------------
    */
    Route::get('/warehouses/data', [WarehouseController::class, 'getData'])->name('warehouses.data');
    Route::resource('warehouses', WarehouseController::class);

    Route::get('/stores/data', [StoreController::class, 'getData'])->name('stores.data');
    Route::resource('stores', StoreController::class);

    /*
    |--------------------------------------------------------------------------
    | MASTER - PRODUCT
    |--------------------------------------------------------------------------
    */
    Route::get('/product_unit/data', [ProductUnitController::class, 'getData'])->name('product_unit.data');
    Route::resource('product_unit', ProductUnitController::class);

    Route::get('/product_type/data', [ProductTypeController::class, 'getData'])->name('product_type.data');
    Route::resource('product_type', ProductTypeController::class);

    Route::get('/sku/data', [SKUController::class, 'getData'])->name('sku.data');
    Route::get('/sku/template/download', [SKUController::class, 'downloadTemplate'])->name('sku.template.download');
    Route::post('/sku/import', [SKUController::class, 'import'])->name('sku.import');
    Route::resource('sku', SKUController::class)->parameters(['sku' => 'kode']);

    Route::get('/product/data', [ProductController::class, 'getData'])->name('product.data');
    Route::get('/filter-sku', [ProductController::class, 'getSku'])->name('product.sku');
    Route::delete('delete-product/{product}', [ProductController::class, 'delete'])->name('product.delete');
    Route::get('/bin-product', [ProductController::class, 'bin'])->name('product.bin');
    Route::get('rollback-product/{product}', [ProductController::class, 'rollback'])->name('product.rollback');
    Route::get('/product/template/download', [ProductController::class, 'downloadTemplate'])->name('product.template.download');
    Route::post('/product/import', [ProductController::class, 'import'])->name('product.import');
    Route::resource('product', ProductController::class);

    /*
    |--------------------------------------------------------------------------
    | STOCK
    |--------------------------------------------------------------------------
    */
    Route::get('/stock_opname/data', [StockOpnameController::class, 'getData'])->name('stock_opname.data');
    Route::get('stock_opname/{stock_opname}/history', [StockOpnameController::class, 'history'])->name('stock_opname.history');
    Route::resource('stock_opname', StockOpnameController::class);

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION - PURCHASE REQUEST
    |--------------------------------------------------------------------------
    */
    Route::get('purchase_request/data', [PurchaseRequestController::class, 'getData'])->name('purchase_request.getData');
    Route::get('/filter-purchase_request', [PurchaseRequestController::class, 'filter'])->name('purchase_request.filter');
    Route::get('purchase_request/{purchase_request}/approve', [PurchaseRequestController::class, 'approve'])->name('purchase_request.approve');
    Route::get('/product-purchase_request', [PurchaseRequestController::class, 'product'])->name('purchase_request.product');
    Route::resource('purchase_request', PurchaseRequestController::class);

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION - PURCHASE ORDER
    |--------------------------------------------------------------------------
    */
    Route::get('/purchase_order/data', [PurchaseOrderController::class, 'getData'])->name('purchase_order.data');
    Route::get('/bin-po/data', [PurchaseOrderController::class, 'binData'])->name('purchase_order.bin.data');
    Route::get('/bin-po', [PurchaseOrderController::class, 'bin'])->name('purchase_order.bin');
    Route::post('/rollback-po', [PurchaseOrderController::class, 'rollback'])->name('purchase_order.rollback');
    Route::delete('delete-po/{purchase_order}', [PurchaseOrderController::class, 'delete'])->name('purchase_order.delete');
    Route::get('purchase_order/{purchase_order}/history', [PurchaseOrderController::class, 'history'])->name('purchase_order.history');
    Route::get('purchase_order/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase_order.approve');
    Route::post('purchase_order/{purchase_order}/confirm', [PurchaseOrderController::class, 'confirm'])->name('purchase_order.confirm');
    Route::get('purchase_order/download', [PurchaseOrderController::class, 'download'])->name('purchase_order.download');
    Route::post('purchase_order/upload', [PurchaseOrderController::class, 'upload'])->name('purchase_order.upload');
    Route::get('/po/{id}/qr/pdf', [PurchaseOrderController::class, 'generateQRPDF'])->name('purchase_order.print');
    Route::post('/purchase_order/{id}/qr/custom-print', [PurchaseOrderController::class, 'generateQRSelected'])->name('purchase_order.custom_print');
    Route::get('/purchase_order/list-existing', [PurchaseOrderController::class, 'listExistingPO'])->name('purchase_order.list_existing');
    Route::resource('purchase_order', PurchaseOrderController::class);
    Route::get('/purchase_order/{id}/print', [PurchaseOrderController::class, 'printPO'])->name('purchase_order.print_po');
    Route::get('/qr/sequence/{id}', [PurchaseOrderController::class, 'getSequence'])->name('qr.sequence');
    Route::post('/qr/reprint/request', [PurchaseOrderController::class, 'requestReprint']);
    Route::get('/qr/reprint/list/{id}', [PurchaseOrderController::class, 'reprintList'])->name('purchase_order.reprint_list');
    Route::get('/qr/reprint/list', [PurchaseOrderController::class, 'listReprint'])->name('reprint.list');
    Route::post('/qr/reprint/approve', [PurchaseOrderController::class, 'approveReprint'])->name('reprint.approve');
    Route::post('/qr/reprint/reject', [PurchaseOrderController::class, 'rejectReprint'])->name('reprint.reject');
    
    /*
    |--------------------------------------------------------------------------
    | TRANSACTION - PRODUCT INBOUND
    |--------------------------------------------------------------------------
    */
    Route::get('/product_inbound', [ProductInboundController::class, 'index'])->name('product_inbound.index');
    Route::get('/product_inbound/datatable', [ProductInboundController::class, 'datatable'])->name('product_inbound.datatable');
    Route::get('/product_inbound/{id}/edit', [ProductInboundController::class, 'edit'])->name('product_inbound.edit');    
    Route::get('/product_inbound/detail/{tgl}', [ProductInboundController::class, 'detailByDate'])->name('product_inbound.detail');
    Route::post('/product_inbound/confirm', [ProductInboundController::class, 'confirm'])->name('product_inbound.confirm');

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION - PRODUCT OUTBOND
    |--------------------------------------------------------------------------
    */
    Route::get('/product_outbound', [ProductOutboundController::class, 'index'])->name('product_outbound.index');
    Route::get('/product_outbound/datatable', [ProductOutboundController::class, 'datatable'])->name('product_outbound.datatable');
    Route::get('/product_outbound/{id}/edit', [ProductOutboundController::class, 'edit'])->name('product_outbound.edit');    
    Route::get('/product_outbound/detail/{tgl}', [ProductOutboundController::class, 'detailByDate'])->name('product_outbound.detail');
    Route::post('/product_outbound/confirm', [ProductOutboundController::class, 'confirm'])->name('product_outbound.confirm');

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION - DELIVERY ORDER & TRANSFER
    |--------------------------------------------------------------------------
    */
    Route::get('/delivery-order/data', [DeliveryOrderController::class, 'data'])->name('delivery_order.data');
    Route::get('/delivery-order/autogen', [DeliveryOrderController::class, 'autoGenerate'])->name('delivery_order.autoGenerate');
    Route::get('delivery_order/{delivery_order}/history', [DeliveryOrderController::class, 'history'])->name('delivery_order.history');
    Route::post('delivery_order/{delivery_order}/approve', [DeliveryOrderController::class, 'approve'])->name('delivery_order.approve');
    Route::get('/cari', [DeliveryOrderController::class, 'search'])->name('delivery_order.search');
    Route::post('delivery_order/upload', [DeliveryOrderController::class, 'upload'])->name('delivery_order.upload');
    Route::post('delivery_order/uploadDO', [DeliveryOrderController::class, 'uploadDO'])->name('delivery_order.uploadDO');
    Route::get('delivery_order/{delivery_order}/downloadDO', [DeliveryOrderController::class, 'downloadDO'])->name('delivery_order.downloadDO');
    Route::delete('delete-do/{delivery_order}', [DeliveryOrderController::class, 'delete2'])->name('delivery_order.delete');
    Route::get('/bin-do', [DeliveryOrderController::class, 'bin2'])->name('delivery_order.bin');
    Route::get('/bin-do-data', [DeliveryOrderController::class, 'bin2Data'])->name('delivery_order.bin2.data');
    Route::post('/rollback-do', [DeliveryOrderController::class, 'rollbackPost'])->name('delivery_order.rollback.post');
    Route::get('/delivery-order/stock', [DeliveryOrderController::class,'getStock']);
    Route::resource('delivery_order', DeliveryOrderController::class);

    Route::get('/product_transfer-data', [DeliveryOrderTransferController::class, 'getData'])->name('product_transfer.getData');
    Route::get('/filter-product_transfer', [DeliveryOrderTransferController::class, 'filter'])->name('product_transfer.filter');
    Route::get('product_transfer/{product_transfer}/history', [DeliveryOrderTransferController::class, 'history'])->name('product_transfer.history');
    Route::get('/product-product_transfer', [DeliveryOrderTransferController::class, 'product'])->name('product_transfer.product');
    Route::resource('product_transfer', DeliveryOrderTransferController::class);

    /*
    |--------------------------------------------------------------------------
    | INVOICE
    |--------------------------------------------------------------------------
    */
    Route::get('/invoice/getdata', [InvoiceController::class, 'getData'])->name('invoice.getdata');
    Route::get('/product-invoice', [InvoiceController::class, 'product'])->name('invoice.product');
    Route::get('invoice/{invoice}/history', [InvoiceController::class, 'history'])->name('invoice.history');
    Route::delete('delete-inv/{invoice}', [InvoiceController::class, 'delete3'])->name('invoice.delete');
    Route::get('/bin-inv', [InvoiceController::class, 'bin3'])->name('invoice.bin');
    Route::get('rollback-inv/{invoice}', [InvoiceController::class, 'rollback3'])->name('invoice.rollback');
    Route::get('Inv_Export2PDF/{invoice}', [InvoiceController::class, 'Export2PDF'])->name('invoice.pdf');
    Route::resource('invoice', InvoiceController::class);

    Route::resource('tax_invoice', TaxInvoiceController::class);
    Route::get('/cariTaxInv', [TaxInvoiceController::class, 'search'])->name('tax_invoice.search');
    Route::get('tax_invoice/{tax_invoice}/history', [TaxInvoiceController::class, 'history'])->name('tax_invoice.history');
    Route::delete('delete-tax-inv/{tax_invoice}', [TaxInvoiceController::class, 'delete3'])->name('tax_invoice.delete');
    Route::get('/bin-tax-inv', [TaxInvoiceController::class, 'bin3'])->name('tax_invoice.bin');
    Route::get('rollback-tax-inv/{tax_invoice}', [TaxInvoiceController::class, 'rollback3'])->name('tax_invoice.rollback');
    Route::get('TaxInv_Export2PDF/{tax_invoice}', [TaxInvoiceController::class, 'Export2PDF'])->name('tax_invoice.pdf');

    Route::resource('receipt_invoice', ReceiptInvoiceController::class);
    Route::get('receipt_invoice/{receipt_invoice}/history', [ReceiptInvoiceController::class, 'history'])->name('receipt_invoice.history');
    Route::delete('delete-rcp-inv/{receipt_invoice}', [ReceiptInvoiceController::class, 'delete4'])->name('receipt_invoice.delete');
    Route::get('/bin-rcp-inv', [ReceiptInvoiceController::class, 'bin4'])->name('receipt_invoice.bin');
    Route::get('rollback-rcp-inv/{receipt_invoice}', [ReceiptInvoiceController::class, 'rollback4'])->name('receipt_invoice.rollback');
    Route::get('RcpInv_Export2PDF/{receipt_invoice}', [ReceiptInvoiceController::class, 'Export2PDF'])->name('receipt_invoice.pdf');

    Route::resource('send_invoice', SendInvoiceController::class);
    Route::get('send_invoice/{send_invoice}/history', [SendInvoiceController::class, 'history'])->name('send_invoice.history');
    Route::get('send_invoice/search', [SendInvoiceController::class, 'search'])->name('send_invoice.search');
    Route::delete('delete/{send_invoice}', [SendInvoiceController::class, 'delete5'])->name('send_invoice.delete');
    Route::get('/bin-send-inv', [SendInvoiceController::class, 'bin5'])->name('send_invoice.bin');
    Route::get('recover/{send_invoice}', [SendInvoiceController::class, 'rollback5'])->name('send_invoice.rollback');

    /*
    |--------------------------------------------------------------------------
    | PAYMENT
    |--------------------------------------------------------------------------
    */
    Route::resource('payment', PaymentController::class);
    Route::get('payment/{payment}/history', [PaymentController::class, 'history'])->name('payment.history');

    /*
    |--------------------------------------------------------------------------
    | REPORT
    |--------------------------------------------------------------------------
    */
    Route::resource('report_payment', ReportPaymentController::class);
    Route::get('/filterRptPayment', [ReportPaymentController::class, 'filter'])->name('report_payment.filter');
    Route::get('RptTaxInv_Export2PDF', [ReportPaymentController::class, 'Export2PDF'])->name('report_payment.pdf');
    Route::get('RptTaxInv_Export2PDFHis/{report_payment}', [ReportPaymentController::class, 'Export2PDFHis'])->name('report_payment.hispdf');

    Route::resource('report_invoicing', ReportInvoicingController::class);
    Route::get('/filterRptInv', [ReportInvoicingController::class, 'filter'])->name('report_invoicing.filter');
    Route::get('RptInv_Export2PDF', [ReportInvoicingController::class, 'Export2PDF'])->name('report_invoicing.pdf');
    
    Route::prefix('report')->group(function () {
        Route::get('stock-movement', [ReportStockMovementController::class, 'index'])
            ->name('stock_movement.index');
    
        Route::get('stock-movement/data', [ReportStockMovementController::class, 'data'])
            ->name('stock_movement.data');
    });

    Route::resource('report_customer', ReportCustomerController::class);
    Route::get('/filterRptCust', [ReportCustomerController::class, 'filter'])->name('report_customer.filter');

    Route::resource('report_courier', ReportCourierController::class);
    Route::get('/filterRptCourier', [ReportCourierController::class, 'filter'])->name('report_courier.filter');

    Route::resource('report_stock_mutation', ReportStockMutationController::class);
    Route::get('/filterRptStockMutation', [ReportStockMutationController::class, 'filter'])->name('report_stock_mutation.filter');

});