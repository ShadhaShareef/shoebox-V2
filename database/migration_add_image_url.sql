-- Migration: Add image_url column to products table
-- Run this if you have already imported database/schema.sql and want to retain your existing data.

USE shoebox_db;

ALTER TABLE products 
  ADD COLUMN image_url VARCHAR(255) NULL DEFAULT NULL AFTER stock_units;
