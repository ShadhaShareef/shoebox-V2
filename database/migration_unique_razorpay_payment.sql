-- Production hardening: prevent duplicate UPI orders from double-submit
USE shoebox_db;

ALTER TABLE orders
  ADD UNIQUE KEY uq_orders_razorpay_payment (razorpay_payment_id);
