import React, { useState, useEffect } from 'react';

interface SettingsProps {
    ajaxUrl: string;
    nonce: string;
}

const Settings: React.FC<SettingsProps> = ({ ajaxUrl, nonce }) => {
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');
    const [settings, setSettings] = useState({
        default_quality: 80,
        auto_convert: false,
        keep_original: true,
        batch_size: 10,
        supported_formats: {
            jpeg: true,
            png: true,
            gif: true,
        },
    });

    useEffect(() => {
        loadSettings();
    }, []);

    const loadSettings = async () => {
        const formData = new FormData();
        formData.append('action', 'get_webp_settings');
        formData.append('nonce', nonce);

        try {
            const response = await fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                setSettings(data.data);
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    };

    const handleSave = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setMessage('');

        const formData = new FormData();
        formData.append('action', 'save_webp_settings');
        formData.append('nonce', nonce);
        formData.append('settings', JSON.stringify(settings));

        try {
            const response = await fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                setMessage('Settings saved successfully!');
            } else {
                setMessage(data.data.message || 'Failed to save settings');
            }
        } catch (error) {
            setMessage('Error: ' + (error as Error).message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="settings-tab">
            <h2>Settings</h2>
            <form onSubmit={handleSave}>
                <table className="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label htmlFor="default_quality">Default Quality</label>
                            </th>
                            <td>
                                <input
                                    type="number"
                                    id="default_quality"
                                    min="1"
                                    max="100"
                                    value={settings.default_quality}
                                    onChange={(e) => setSettings({ ...settings, default_quality: parseInt(e.target.value) })}
                                />
                                <p className="description">Quality for WebP conversion (1-100)</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label htmlFor="batch_size">Batch Size</label>
                            </th>
                            <td>
                                <input
                                    type="number"
                                    id="batch_size"
                                    min="1"
                                    max="100"
                                    value={settings.batch_size}
                                    onChange={(e) => setSettings({ ...settings, batch_size: parseInt(e.target.value) })}
                                />
                                <p className="description">Number of images to process per batch in bulk conversion</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">Auto Convert on Upload</th>
                            <td>
                                <label>
                                    <input
                                        type="checkbox"
                                        checked={settings.auto_convert}
                                        onChange={(e) => setSettings({ ...settings, auto_convert: e.target.checked })}
                                    />
                                    Automatically convert images to WebP when uploaded
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">Keep Original Images</th>
                            <td>
                                <label>
                                    <input
                                        type="checkbox"
                                        checked={settings.keep_original}
                                        onChange={(e) => setSettings({ ...settings, keep_original: e.target.checked })}
                                    />
                                    Keep original images after conversion
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">Supported Formats</th>
                            <td>
                                <fieldset>
                                    <label>
                                        <input
                                            type="checkbox"
                                            checked={settings.supported_formats.jpeg}
                                            onChange={(e) => setSettings({
                                                ...settings,
                                                supported_formats: { ...settings.supported_formats, jpeg: e.target.checked }
                                            })}
                                        />
                                        JPEG
                                    </label>
                                    <br />
                                    <label>
                                        <input
                                            type="checkbox"
                                            checked={settings.supported_formats.png}
                                            onChange={(e) => setSettings({
                                                ...settings,
                                                supported_formats: { ...settings.supported_formats, png: e.target.checked }
                                            })}
                                        />
                                        PNG
                                    </label>
                                    <br />
                                    <label>
                                        <input
                                            type="checkbox"
                                            checked={settings.supported_formats.gif}
                                            onChange={(e) => setSettings({
                                                ...settings,
                                                supported_formats: { ...settings.supported_formats, gif: e.target.checked }
                                            })}
                                        />
                                        GIF
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p className="submit">
                    <button type="submit" className="button button-primary" disabled={loading}>
                        {loading ? 'Saving...' : 'Save Settings'}
                    </button>
                </p>
            </form>

            {message && (
                <div className={`notice notice-${message.includes('success') ? 'success' : 'error'}`}>
                    <p>{message}</p>
                </div>
            )}
        </div>
    );
};

export default Settings;
