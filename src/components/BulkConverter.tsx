import React, { useState } from 'react';

interface BulkConverterProps {
    ajaxUrl: string;
    nonce: string;
}

const BulkConverter: React.FC<BulkConverterProps> = ({ ajaxUrl, nonce }) => {
    const [loading, setLoading] = useState(false);
    const [quality, setQuality] = useState(80);
    const [deleteOriginal, setDeleteOriginal] = useState(false);
    const [message, setMessage] = useState('');
    const [stats, setStats] = useState<any>(null);
    const [progress, setProgress] = useState('');

    const processBatch = async (page: number, totalStats: any) => {
        const formData = new FormData();
        formData.append('action', 'bulk_convert_images');
        formData.append('nonce', nonce);
        formData.append('quality', quality.toString());
        formData.append('delete_original', deleteOriginal ? '1' : '0');
        formData.append('page', page.toString());

        const response = await fetch(ajaxUrl, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            const batchStats = data.data.stats;
            
            // Accumulate stats
            totalStats.converted += batchStats.converted;
            totalStats.failed += batchStats.failed;
            totalStats.skipped += batchStats.skipped;
            
            const processed = totalStats.converted + totalStats.failed + totalStats.skipped;
            setProgress(`Processing: ${processed} / ${batchStats.total}`);

            if (data.data.has_more) {
                // Process next batch
                await processBatch(data.data.next_page, totalStats);
            } else {
                // All done
                setStats(totalStats);
                setMessage('Bulk conversion completed');
                setProgress('');
                setLoading(false);
            }
        } else {
            throw new Error(data.data.message || 'Batch failed');
        }
    };

    const handleBulkConvert = async () => {
        if (!confirm('This will convert all images in your media library. Continue?')) {
            return;
        }

        setLoading(true);
        setMessage('');
        setStats(null);
        setProgress('Starting...');

        try {
            const totalStats = {
                total: 0,
                converted: 0,
                failed: 0,
                skipped: 0,
            };

            await processBatch(1, totalStats);
        } catch (error) {
            setMessage('Error: ' + (error as Error).message);
            setProgress('');
            setLoading(false);
        }
    };

    return (
        <div className="bulk-converter">
            <h2>Bulk Converter</h2>
            <p>Convert all images in your media library to WebP format.</p>

            <div className="settings-group">
                <div className="setting-item">
                    <label htmlFor="quality">Quality (1-100):</label>
                    <input
                        type="number"
                        id="quality"
                        min="1"
                        max="100"
                        value={quality}
                        onChange={(e) => setQuality(parseInt(e.target.value))}
                        disabled={loading}
                    />
                </div>

                <div className="setting-item">
                    <label>
                        <input
                            type="checkbox"
                            checked={deleteOriginal}
                            onChange={(e) => setDeleteOriginal(e.target.checked)}
                            disabled={loading}
                        />
                        Delete original images after conversion
                    </label>
                </div>
            </div>

            <button
                onClick={handleBulkConvert}
                className="button button-primary"
                disabled={loading}
            >
                {loading ? 'Converting...' : 'Start Bulk Conversion'}
            </button>

            {progress && (
                <div className="progress-message">
                    <p><strong>{progress}</strong></p>
                </div>
            )}

            {message && (
                <div className={`notice notice-${stats ? 'success' : 'error'}`}>
                    <p>{message}</p>
                </div>
            )}

            {stats && (
                <div className="conversion-stats">
                    <h3>Conversion Statistics:</h3>
                    <ul>
                        <li>Total Images: {stats.total}</li>
                        <li>Successfully Converted: {stats.converted}</li>
                        <li>Failed: {stats.failed}</li>
                        <li>Skipped: {stats.skipped}</li>
                    </ul>
                </div>
            )}
        </div>
    );
};

export default BulkConverter;
