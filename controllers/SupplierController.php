<?php
/**
 * Supplier Controller
 * Handles Suppliers, Purchase Orders, and Supplier Payments CRUD
 */

session_start();
require_once '../includes/db.php';
require_once '../includes/queries.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$company_id = $_SESSION['company_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'] ?? '';

// ============================================================
// ── SUPPLIERS ────────────────────────────────────────────────
// ============================================================

// ── SAVE SUPPLIER (Add / Edit) ──
if ($action === 'save_supplier') {
    $id       = (int)($_POST['id'] ?? 0);
    $name     = trim($_POST['name']     ?? '');
    $category = trim($_POST['category'] ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $email    = trim($_POST['email']    ?? '');
    $address  = trim($_POST['address']  ?? '');
    $notes    = trim($_POST['notes']    ?? '');
    $status   = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';

    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Supplier name is required.']);
        exit;
    }

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE suppliers SET name=?,category=?,phone=?,email=?,address=?,notes=?,status=? WHERE id=? AND company_id=?");
            $stmt->execute([$name,$category,$phone,$email,$address,$notes,$status,$id,$company_id]);
            logActivity($pdo, $company_id, $_SESSION['user_id'], 'supplier_update', "Updated supplier: $name");
            echo json_encode(['status'=>'success','message'=>'Supplier updated successfully!']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO suppliers (company_id,name,category,phone,email,address,notes,status) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$company_id,$name,$category,$phone,$email,$address,$notes,$status]);
            $newId = $pdo->lastInsertId();
            logActivity($pdo, $company_id, $_SESSION['user_id'], 'supplier_create', "Added new supplier: $name");
            echo json_encode(['status'=>'success','message'=>'Supplier added successfully!','id'=>$newId]);
        }
    } catch (PDOException $e) {
        echo json_encode(['status'=>'error','message'=>'Database error: '.$e->getMessage()]);
    }

// ── GET SINGLE SUPPLIER ──
} elseif ($action === 'get_supplier') {
    $id = (int)($_POST['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id=? AND company_id=?");
        $stmt->execute([$id,$company_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo json_encode(['status'=>'success','data'=>$row]);
        } else {
            echo json_encode(['status'=>'error','message'=>'Supplier not found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }

// ── DELETE SUPPLIER ──
} elseif ($action === 'delete_supplier') {
    $id = (int)($_POST['id'] ?? 0);
    try {
        // Check for linked POs first
        $check = $pdo->prepare("SELECT COUNT(*) FROM purchase_orders WHERE supplier_id=? AND company_id=?");
        $check->execute([$id,$company_id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['status'=>'error','message'=>'Cannot delete: this supplier has purchase orders linked to it.']);
            exit;
        }
        $nStmt = $pdo->prepare("SELECT name FROM suppliers WHERE id=? AND company_id=?");
        $nStmt->execute([$id, $company_id]);
        $sName = $nStmt->fetchColumn() ?: "ID $id";
        
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id=? AND company_id=?");
        $stmt->execute([$id,$company_id]);
        logActivity($pdo, $company_id, $_SESSION['user_id'], 'supplier_delete', "Deleted supplier: $sName");
        echo json_encode(['status'=>'success','message'=>'Supplier deleted successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['status'=>'error','message'=>'Could not delete supplier.']);
    }

// ── GET ALL SUPPLIERS ──
} elseif ($action === 'get_suppliers') {
    try {
        $stmt = $pdo->prepare("
            SELECT s.*,
                   COUNT(DISTINCT po.id)          AS po_count,
                   COALESCE(SUM(po.total),0)       AS po_total
            FROM suppliers s
            LEFT JOIN purchase_orders po ON po.supplier_id=s.id AND po.company_id=s.company_id
            WHERE s.company_id=?
            GROUP BY s.id
            ORDER BY s.name ASC
        ");
        $stmt->execute([$company_id]);
        echo json_encode(['status'=>'success','data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (PDOException $e) {
        echo json_encode(['status'=>'error','data'=>[],'message'=>$e->getMessage()]);
    }

// ============================================================
// ── PURCHASE ORDERS ──────────────────────────────────────────
// ============================================================

// ── SAVE PO (Add / Edit) ──
} elseif ($action === 'save_po') {
    $id            = (int)($_POST['id']          ?? 0);
    $supplier_id   = (int)($_POST['supplier_id'] ?? 0);
    $order_date    = trim($_POST['order_date']    ?? date('Y-m-d'));
    $expected_date = trim($_POST['expected_date'] ?? '');
    $items         = (int)($_POST['items']        ?? 0);
    $total         = (float)($_POST['total']      ?? 0);
    $notes         = trim($_POST['notes']         ?? '');
    $status        = in_array($_POST['status'] ?? '', ['pending','received','cancelled']) ? $_POST['status'] : 'pending';

    if ($supplier_id <= 0) {
        echo json_encode(['status'=>'error','message'=>'Please select a supplier.']);
        exit;
    }
    if ($total <= 0) {
        echo json_encode(['status'=>'error','message'=>'Total must be greater than zero.']);
        exit;
    }

    // Verify the supplier belongs to this company
    $chk = $pdo->prepare("SELECT id FROM suppliers WHERE id=? AND company_id=?");
    $chk->execute([$supplier_id,$company_id]);
    if (!$chk->fetch()) {
        echo json_encode(['status'=>'error','message'=>'Invalid supplier.']);
        exit;
    }

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE purchase_orders SET supplier_id=?,order_date=?,expected_date=?,items=?,total=?,status=?,notes=? WHERE id=? AND company_id=?");
            $stmt->execute([$supplier_id,$order_date,$expected_date?:null,$items,$total,$status,$notes,$id,$company_id]);
            echo json_encode(['status'=>'success','message'=>'Purchase order updated!']);
        } else {
            // Auto-generate PO number: PO-YYYY-XXXX
            $year  = date('Y');
            $countStmt = $pdo->prepare("SELECT COUNT(*)+1 AS n FROM purchase_orders WHERE company_id=? AND YEAR(order_date)=?");
            $countStmt->execute([$company_id,$year]);
            $seq    = str_pad($countStmt->fetchColumn(), 4, '0', STR_PAD_LEFT);
            $po_num = "PO-{$year}-{$seq}";

            $stmt = $pdo->prepare("INSERT INTO purchase_orders (company_id,supplier_id,po_number,order_date,expected_date,items,total,status,notes) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$company_id,$supplier_id,$po_num,$order_date,$expected_date?:null,$items,$total,$status,$notes]);
            $newId = $pdo->lastInsertId();
            echo json_encode(['status'=>'success','message'=>"Purchase order {$po_num} created!",'id'=>$newId,'po_number'=>$po_num]);
            logActivity($pdo, $company_id, $_SESSION['user_id'], 'po_create', "Created Purchase Order: {$po_num}");
        }
    } catch (PDOException $e) {
        echo json_encode(['status'=>'error','message'=>'Database error: '.$e->getMessage()]);
    }

// ── GET SINGLE PO ──
} elseif ($action === 'get_po') {
    $id = (int)($_POST['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("SELECT po.*, s.name AS supplier_name FROM purchase_orders po JOIN suppliers s ON po.supplier_id=s.id WHERE po.id=? AND po.company_id=?");
        $stmt->execute([$id,$company_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo $row ? json_encode(['status'=>'success','data'=>$row]) : json_encode(['status'=>'error','message'=>'PO not found.']);
    } catch (PDOException $e) {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }

// ── UPDATE PO STATUS ──
} elseif ($action === 'update_po_status') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['pending','received','cancelled']) ? $_POST['status'] : 'pending';
    try {
        $stmt = $pdo->prepare("UPDATE purchase_orders SET status=? WHERE id=? AND company_id=?");
        $stmt->execute([$status,$id,$company_id]);
        $poNumStmt = $pdo->prepare("SELECT po_number FROM purchase_orders WHERE id=? AND company_id=?");
        $poNumStmt->execute([$id, $company_id]);
        $poNum = $poNumStmt->fetchColumn() ?: "ID $id";
        logActivity($pdo, $company_id, $_SESSION['user_id'], 'po_status', "Updated PO {$poNum} status to ".ucfirst($status));
        echo json_encode(['status'=>'success','message'=>'Status updated to '.ucfirst($status).'.']);
    } catch (PDOException $e) {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }

// ── GET PURCHASE ORDERS ──
} elseif ($action === 'get_purchase_orders') {
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);
    try {
        $sql = "SELECT po.*, s.name AS supplier_name
                FROM purchase_orders po
                JOIN suppliers s ON po.supplier_id=s.id
                WHERE po.company_id=?";
        $params = [$company_id];
        if ($supplier_id > 0) { $sql .= " AND po.supplier_id=?"; $params[] = $supplier_id; }
        $sql .= " ORDER BY po.created_at DESC LIMIT 300";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['status'=>'success','data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (PDOException $e) {
        echo json_encode(['status'=>'error','data'=>[],'message'=>$e->getMessage()]);
    }

// ── DELETE PO ──
} elseif ($action === 'delete_po') {
    $id = (int)($_POST['id'] ?? 0);
    try {
        // unlink related payments first (ON DELETE SET NULL handles it, but let's be explicit)
        $pdo->prepare("UPDATE supplier_payments SET purchase_order_id=NULL WHERE purchase_order_id=? AND company_id=?")->execute([$id,$company_id]);
        
        $poNumStmt = $pdo->prepare("SELECT po_number FROM purchase_orders WHERE id=? AND company_id=?");
        $poNumStmt->execute([$id, $company_id]);
        $poNum = $poNumStmt->fetchColumn() ?: "ID $id";
        
        $pdo->prepare("DELETE FROM purchase_orders WHERE id=? AND company_id=?")->execute([$id,$company_id]);
        logActivity($pdo, $company_id, $_SESSION['user_id'], 'po_delete', "Deleted PO: {$poNum}");
        echo json_encode(['status'=>'success','message'=>'Purchase order deleted.']);
    } catch (PDOException $e) {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }

// ============================================================
// ── SUPPLIER PAYMENTS ─────────────────────────────────────────
// ============================================================

// ── SAVE PAYMENT ──
} elseif ($action === 'save_payment') {
    $supplier_id       = (int)($_POST['supplier_id']       ?? 0);
    $purchase_order_id = (int)($_POST['purchase_order_id'] ?? 0);
    $amount            = (float)($_POST['amount']          ?? 0);
    $payment_date      = trim($_POST['payment_date']        ?? date('Y-m-d'));
    $method            = trim($_POST['method']              ?? 'Cash');
    $reference         = trim($_POST['reference']           ?? '');
    $notes             = trim($_POST['notes']               ?? '');
    $pmStatus          = in_array($_POST['pm_status'] ?? 'paid', ['paid','pending']) ? $_POST['pm_status'] : 'paid';

    if ($supplier_id <= 0) {
        echo json_encode(['status'=>'error','message'=>'Please select a supplier.']);
        exit;
    }
    if ($amount <= 0) {
        echo json_encode(['status'=>'error','message'=>'Payment amount must be greater than zero.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO supplier_payments (company_id,supplier_id,purchase_order_id,amount,payment_date,method,reference,notes,status) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $company_id,
            $supplier_id,
            $purchase_order_id > 0 ? $purchase_order_id : null,
            $amount,
            $payment_date,
            $method,
            $reference,
            $notes,
            $pmStatus
        ]);
        logActivity($pdo, $company_id, $_SESSION['user_id'], 'payment_create', "Recorded support payment of {$amount} to Supplier ID {$supplier_id}");
        echo json_encode(['status'=>'success','message'=>'Payment recorded successfully!','id'=>$pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['status'=>'error','message'=>'Database error: '.$e->getMessage()]);
    }

// ── GET PAYMENTS ──
} elseif ($action === 'get_payments') {
    $supplier_id = (int)($_POST['supplier_id'] ?? 0);
    try {
        $sql = "SELECT sp.*, s.name AS supplier_name,
                       po.po_number
                FROM supplier_payments sp
                JOIN suppliers s ON sp.supplier_id=s.id
                LEFT JOIN purchase_orders po ON sp.purchase_order_id=po.id
                WHERE sp.company_id=?";
        $params = [$company_id];
        if ($supplier_id > 0) { $sql .= " AND sp.supplier_id=?"; $params[] = $supplier_id; }
        $sql .= " ORDER BY sp.payment_date DESC, sp.created_at DESC LIMIT 300";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['status'=>'success','data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (PDOException $e) {
        echo json_encode(['status'=>'error','data'=>[],'message'=>$e->getMessage()]);
    }

// ── DELETE PAYMENT ──
} elseif ($action === 'delete_payment') {
    $id = (int)($_POST['id'] ?? 0);
    try {
        $pdo->prepare("DELETE FROM supplier_payments WHERE id=? AND company_id=?")->execute([$id,$company_id]);
        logActivity($pdo, $company_id, $_SESSION['user_id'], 'payment_delete', "Deleted supplier payment ID {$id}");
        echo json_encode(['status'=>'success','message'=>'Payment record deleted.']);
    } catch (PDOException $e) {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }

} else {
    echo json_encode(['status'=>'error','message'=>'Unknown action: '.$action]);
}
?>
