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
SET trades.original_trade_id = tt.original_trade_id
WHERE trades.parent_trade_id = tt.id
  AND trades.original_trade_id IS NULL
  AND tt.original_trade_id IS NOT NULL;
# Drop temp table
DROP TABLE temp_trades;

COMMIT;