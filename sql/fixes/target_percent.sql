START TRANSACTION;
# Create temp table
CREATE TEMPORARY TABLE temp_trades (
  id INT UNSIGNED,
  parent_trade_id INT UNSIGNED,
  original_trade_id INT UNSIGNED,
  target_percent DOUBLE,
  target_shrink_differential DOUBLE
);
INSERT INTO temp_trades (id, parent_trade_id, original_trade_id, target_percent, target_shrink_differential)
  SELECT id, parent_trade_id, original_trade_id, target_percent, target_shrink_differential
  FROM trades;
# Update target_* from parent_trade_id for bought trades
UPDATE trades, temp_trades AS tt
SET trades.target_percent = tt.target_percent,
    trades.target_shrink_differential = tt.target_shrink_differential
WHERE trades.status = 'Bought'
    AND trades.target_percent IS NULL
    AND trades.target_shrink_differential IS NULL
    AND trades.parent_trade_id = tt.id;
# Drop temp table
DROP TABLE temp_trades;
# Create temp table so we work with fresh data
CREATE TEMPORARY TABLE temp_trades (
  id INT UNSIGNED,
  parent_trade_id INT UNSIGNED,
  original_trade_id INT UNSIGNED,
  target_percent DOUBLE,
  target_shrink_differential DOUBLE
);
INSERT INTO temp_trades (id, parent_trade_id, original_trade_id, target_percent, target_shrink_differential)
  SELECT id, parent_trade_id, original_trade_id, target_percent, target_shrink_differential
  FROM trades;
# Update target_* from original_trade_id for sell-order trades
UPDATE trades, temp_trades AS tt
SET trades.target_percent = tt.target_percent,
    trades.target_shrink_differential = tt.target_shrink_differential
WHERE trades.status = 'Sell-Order'
      AND trades.target_percent IS NULL
      AND trades.target_shrink_differential IS NULL
      AND trades.original_trade_id = tt.id;
# Drop temp table
DROP TABLE temp_trades;
# Create temp table so we work with fresh data
CREATE TEMPORARY TABLE temp_trades (
  id INT UNSIGNED,
  parent_trade_id INT UNSIGNED,
  original_trade_id INT UNSIGNED,
  target_percent DOUBLE,
  target_shrink_differential DOUBLE
);
INSERT INTO temp_trades (id, parent_trade_id, original_trade_id, target_percent, target_shrink_differential)
  SELECT id, parent_trade_id, original_trade_id, target_percent, target_shrink_differential
  FROM trades;
# Update target_* from parent_trade_id for sold orders
UPDATE trades, temp_trades AS tt
SET trades.target_percent = tt.target_percent,
    trades.target_shrink_differential = tt.target_shrink_differential
WHERE trades.status = 'Sold'
      AND trades.target_percent IS NULL
      AND trades.target_shrink_differential IS NULL
      AND trades.parent_trade_id = tt.id;
# Drop temp table
DROP TABLE temp_trades;

COMMIT;