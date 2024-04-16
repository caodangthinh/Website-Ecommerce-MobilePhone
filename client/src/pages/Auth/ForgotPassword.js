import React, { useState } from 'react';
import Layout from '../../components/Layout/layout';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';

const ForgotPassword = () => {
    const [email, setEmail] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [answer, setAnswer] = useState('');
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await axios.post(`${process.env.REACT_APP_API}/api/v1/auth/forgot-password`, { email, newPassword, answer });
            if (res && res.data.success) {
                toast.success(res.data && res.data.message);

                navigate('/login');
            } else {
                toast.error(res.data.message);
            }

        } catch (error) {
            console.log(error);
            toast.error('Error')
        }

    };
    return (
        <Layout title={'Forgot Password - PhoneShop'}>
            <div className="wrapper" id="container">
                <div className="form-container login-container">
                    <div className="form-login-sign">
                        <form onSubmit={handleSubmit} style={{marginTop: '10px'}}>
                            <div className="title-form">
                                <h1>Reset Password</h1>
                            </div>
                            <div className="field">
                                <input type="email" onChange={(e) => setEmail(e.target.value)} value={email} placeholder="Enter your Email" className="input" id="email" name="email" required />
                            </div>
                            <div className="field">
                                <input type="text" onChange={(e) => setAnswer(e.target.value)} value={answer} placeholder="Enter your Answer" className="password" id="password" name="password" required />
                            </div>
                            
                            <div className="field">
                                <input type="text" onChange={(e) => setNewPassword(e.target.value)} value={newPassword} placeholder="Enter your Password" className="password" id="password" name="password" required />
                            </div>
                            <div className="pass-link">
                                <a href="/forgot-password">Forgot password?</a>
                            </div>
                            <button id="btnLogin" className="btn-form">Save</button>
                        </form>
                    </div>
                    
                </div>
            </div>
        </Layout>
    )
}

export default ForgotPassword
