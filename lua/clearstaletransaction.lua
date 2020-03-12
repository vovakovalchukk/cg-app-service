local function deleteTxAction()
    return redis.call('DEL', KEYS[2])
end

local function deleteTransaction()
    return redis.call('DEL', KEYS[1])
end

local function getTransactionTimestamp()
    return tonumber(redis.call('GET', KEYS[1])) or 0
end

local function transactionDoesntExist(transactionTimestamp)
    return transactionTimestamp == 0
end

local function txActionOlderThanTransaction(txActionTimestamp, transactionTimestamp)
    return transactionTimestamp > txActionTimestamp
end

local function transactionIsTooOld(transactionTimestamp, cutoffTimestamp)
    return transactionTimestamp <= cutoffTimestamp
end

local function getOutputStatus(currentStatus, newStatus)
    return currentStatus or newStatus
end

local txActionTimestamp = tonumber(ARGV[1])
local cutoffTimestamp = tonumber(ARGV[2])
local transactionTimestamp = getTransactionTimestamp()
local status = 0

if transactionDoesntExist(transactionTimestamp) then
    return getOutputStatus(status, deleteTxAction())
end

if txActionOlderThanTransaction(txActionTimestamp, transactionTimestamp) then
    status = getOutputStatus(status, deleteTxAction())
end

if transactionIsTooOld(transactionTimestamp, cutoffTimestamp) then
    status = getOutputStatus(status, deleteTransaction())
end

return status
