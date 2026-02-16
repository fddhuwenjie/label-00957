-- 更新现有音乐数据的音频URL
-- 运行此脚本来为示例数据添加音频URL

UPDATE music SET audio_url = '/assets/audio/sample1.mp3' WHERE id = 1;
UPDATE music SET audio_url = '/assets/audio/sample2.mp3' WHERE id = 2;
UPDATE music SET audio_url = '/assets/audio/sample3.mp3' WHERE id = 3;
UPDATE music SET audio_url = '/assets/audio/sample4.mp3' WHERE id = 4;
UPDATE music SET audio_url = '/assets/audio/sample5.mp3' WHERE id = 5;
UPDATE music SET audio_url = '/assets/audio/sample6.mp3' WHERE id = 6;

-- 或者批量更新所有空的audio_url
-- UPDATE music SET audio_url = CONCAT('/assets/audio/sample', id, '.mp3') WHERE audio_url = '' OR audio_url IS NULL;
