-- Add razorpay_payment_id (run once if you imported schema before this column existed)
USE shoebox_db;

ALTER TABLE orders
  ADD COLUMN razorpay_payment_id VARCHAR(80) NULL AFTER razorpay_order_id;
